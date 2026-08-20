// Planet Host Game Node Agent — Windows Tray Manager + Service Host
// Single binary, three modes:
//   (no args)              -> system-tray controller (icon in taskbar)
//   --service              -> run by the Windows Service Control Manager,
//                             spawns ph-agent.exe as a child (stays online
//                             24/7 across logoff/reboot)
//   --uninstall            -> silent removal (service + task + shortcuts + files)
//
// Also used by the GUI installer for: adding the desktop/startup/start-menu
// shortcuts, installing/removing the Windows service, and managing the list of
// install locations (drives) that the agent stores game servers on.
//
// Build (Windows, .NET Framework 4.x csc):
//   csc.exe /nologo /t:winexe /out:ph-agent-tray.exe /main:TrayManager.Program
//       /r:System.Windows.Forms.dll /r:System.Drawing.dll
//       /r:System.ServiceProcess.dll /r:Microsoft.CSharp.dll
//       /r:System.Management.dll
//       TrayManager.cs
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.Globalization;
using System.IO;
using System.Threading;
using System.Windows.Forms;
using System.ServiceProcess;
using Microsoft.Win32;
using System.Management;

namespace TrayManager
{
    public class Program
    {
        public const string AgentExe = "ph-agent.exe";
        public const string ServiceName = "PHGameNodeAgent";
        public const string TaskName = "PlanetHostsAgent";
        public const string RunKey = "PlanetHostsAgentTray";
        public static string ExePath = Application.ExecutablePath;
        public static string AppDir = Path.GetDirectoryName(Application.ExecutablePath);
        public static string ConfigPath = Path.Combine(AppDir, "agent-config.json");
        public static string StatusPath = Path.Combine(AppDir, "agent-status.json");
        public static string AgentPath = Path.Combine(AppDir, AgentExe);

        [STAThread]
        public static int Main(string[] args)
        {
            try { ServicePointManager2(); } catch { }
            // Surface runtime errors instead of the tray icon silently vanishing.
            try { Application.SetUnhandledExceptionMode(UnhandledExceptionMode.CatchException); }
            catch { }
            Application.ThreadException += delegate(object s, System.Threading.ThreadExceptionEventArgs e)
            {
                CrashLog("thread", e.Exception);
            };
            try { AppDomain.CurrentDomain.UnhandledException += delegate(object s, UnhandledExceptionEventArgs e)
            {
                if (e.ExceptionObject is Exception) CrashLog("domain", (Exception)e.ExceptionObject);
            }; }
            catch { }

            bool has = false;
            foreach (string a in args)
                if (a == "--service" || a == "/service" || a == "-service") has = true;
            if (has)
            {
                ServiceBase.Run(new ServiceBase[] { new AgentService() });
                return 0;
            }
            bool un = false;
            foreach (string a in args)
                if (a == "--uninstall" || a == "/uninstall" || a == "-uninstall") un = true;
            if (un)
            {
                SilentUninstall();
                return 0;
            }
            // Only one tray instance — a second launch just exits immediately.
            bool singleton = false;
            Mutex mux = null;
            try { mux = new Mutex(true, "PlanetHostsAgentTrayMutex", out singleton); } catch { }
            if (!singleton)
            {
                // Already running (auto-start + manual launch again) — do nothing.
                return 0;
            }
            // --setup [service|task]  used by the GUI installer after files +
            // config are in place: create shortcuts/startup entry, then register
            // the chosen run mode (Windows service recommended, or scheduled task)
            bool setup = false;
            foreach (string a in args)
                if (a == "--setup" || a == "/setup") setup = true;
            if (setup)
            {
                try
                {
                    CreateAllShortcuts();
                    if (File.Exists(Path.Combine(AppDir, AgentExe)))
                    {
                        bool svcMode = false;
                        foreach (string a in args)
                            if (a == "service" || a == "--service-mode") svcMode = true;
                        if (svcMode) { UnregisterTask(); InstallService(); }
                        else { UninstallService(); RegisterTask(); }
                        StartAgent();
                    }
                }
                catch { }
                return 0;
            }
            try
            {
                Application.EnableVisualStyles();
                Application.SetCompatibleTextRenderingDefault(false);
                using (TrayApp app = new TrayApp())
                {
                    Application.Run(app);
                }
            }
            catch (Exception ex)
            {
                CrashLog("main", ex);
                MessageBox.Show("Planet Host Game Node Agent failed to start:\n\n" + ex.Message
                    + "\n\nDetails were written to " + Path.Combine(AppDir, "agent-error.log"),
                    "Planet Host Game Node Agent", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            return 0;
        }

        // Writes an error log next to the exe so a silent crash leaves a record.
        private static void CrashLog(string where, Exception e)
        {
            try
            {
                File.AppendAllText(Path.Combine(AppDir, "agent-error.log"),
                    DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss") + " [" + where + "] "
                    + (e == null ? "null" : (e.ToString())) + "\r\n");
            }
            catch { }
        }

        // Place after the namespace line so it compiles with the Math using above
        private static void ServicePointManager2()
        {
            try
            {
                System.Net.ServicePointManager.SecurityProtocol =
                    System.Net.SecurityProtocolType.Tls12 |
                    System.Net.SecurityProtocolType.Tls11 |
                    System.Net.SecurityProtocolType.Tls;
            }
            catch { }
        }

        // ───────────────────────── Path helpers ─────────────────────────
        public static string DesktopDir()
        {
            return Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory);
        }
        public static string StartMenuDir()
        {
            string p = Environment.GetFolderPath(Environment.SpecialFolder.Programs);
            string dir = Path.Combine(p, "Planet Host Agent");
            try { Directory.CreateDirectory(dir); } catch { }
            return dir;
        }
        public static string StartupDir()
        {
            return Environment.GetFolderPath(Environment.SpecialFolder.Startup);
        }

        // ───────────────────────── Config ─────────────────────────
        public class Config
        {
            public string panel_url = "";
            public string node_token = "";
            public List<String> locations = new List<String>();
            public string base_dir = "";
            public bool loaded = false;
            public string loadError = "";
        }

        public static Config LoadConfig()
        {
            Config c = new Config();
            if (!File.Exists(ConfigPath))
            {
                c.loadError = "agent-config.json not found beside this program.";
                return c;
            }
            try
            {
                JObj o = (JObj)ParseJson(File.ReadAllText(ConfigPath));
                c.panel_url = Str(o, "panel_url", "");
                c.node_token = Str(o, "node_token", "");
                c.base_dir = Str(o, "base_dir", "");
                c.loaded = true;
                // locations can be ["D:\\Games"] or [{"path":"D:\\Games"}]
                object loc = Get(o, "locations");
                if (loc is JArr)
                {
                    JArr arr = (JArr)loc;
                    Dictionary<string, string> seen = new Dictionary<string, string>();
                    foreach (object item in arr.Items)
                    {
                        string p = "";
                        if (item is string) p = (string)item;
                        else if (item is JObj) p = Str((JObj)item, "path", "");
                        p = p.Trim();
                        if (p.Length == 0) continue;
                        try { p = Path.GetFullPath(p); } catch { }
                        if (!seen.ContainsKey(p)) { seen[p] = ""; c.locations.Add(p); }
                    }
                }
                else if (c.base_dir.Length > 0)
                {
                    c.locations.Add(c.base_dir);
                }
                if (c.locations.Count == 0)
                    c.locations.Add(@"C:\PlanetHostsGames");
            }
            catch (Exception ex)
            {
                c.loadError = ex.Message;
            }
            return c;
        }

        public static bool SaveConfig(Config c)
        {
            try
            {
                List<string> locs = new List<string>();
                foreach (string l in c.locations) locs.Add("\"" + JsonEsc(l) + "\"");
                string json = "{"
                    + "\"panel_url\":\"" + JsonEsc(c.panel_url) + "\","
                    + "\"node_token\":\"" + JsonEsc(c.node_token) + "\","
                    + "\"locations\":[" + String.Join(",", locs.ToArray()) + "],"
                    + "\"base_dir\":\"" + JsonEsc(c.base_dir) + "\","
                    + "\"poll_interval_ms\":10000,"
                    + "\"steamcmd\":\"steamcmd.exe\","
                    + "\"steam_user\":\"anonymous\","
                    + "\"steam_pass\":\"\""
                    + "}";
                File.WriteAllText(ConfigPath, json);
                return true;
            }
            catch { return false; }
        }

        static string JsonEsc(string s)
        {
            return (s ?? "").Replace("\\", "\\\\").Replace("\"", "\\\"");
        }

        // Minimal JSON reader (keeps this single-file build dependency-free)
        class JObj { public Dictionary<string, object> M = new Dictionary<string, object>(); }
        class JArr { public List<object> Items = new List<object>(); }
        static string Str(JObj o, string k, string dflt)
        {
            object v;
            if (o.M.TryGetValue(k, out v) && v is string) return (string)v;
            return dflt;
        }
        static object Get(JObj o, string k) { object v; if (o.M.TryGetValue(k, out v)) return v; return null; }

        static object ParseJson(string text)
        {
            int i = 0;
            return ParseValue(text, ref i);
        }
        static object ParseValue(string t, ref int i)
        {
            SkipWs(t, ref i);
            if (i >= t.Length) return null;
            char ch = t[i];
            if (ch == '{')
            {
                i++;
                JObj o = new JObj();
                SkipWs(t, ref i);
                if (i < t.Length && t[i] == '}') { i++; return o; }
                while (true)
                {
                    SkipWs(t, ref i);
                    object k = ParseValue(t, ref i);
                    string key = (k is string) ? (string)k : "";
                    SkipWs(t, ref i);
                    if (i < t.Length && t[i] == ':') i++;
                    object v = ParseValue(t, ref i);
                    o.M[key] = v;
                    SkipWs(t, ref i);
                    if (i < t.Length && t[i] == ',') { i++; continue; }
                    if (i < t.Length && t[i] == '}') { i++; break; }
                    break;
                }
                return o;
            }
            if (ch == '[')
            {
                i++;
                JArr a = new JArr();
                SkipWs(t, ref i);
                if (i < t.Length && t[i] == ']') { i++; return a; }
                while (true)
                {
                    object v = ParseValue(t, ref i);
                    a.Items.Add(v);
                    SkipWs(t, ref i);
                    if (i < t.Length && t[i] == ',') { i++; continue; }
                    if (i < t.Length && t[i] == ']') { i++; break; }
                    break;
                }
                return a;
            }
            if (ch == '"')
            {
                i++;
                System.Text.StringBuilder sb = new System.Text.StringBuilder();
                while (i < t.Length)
                {
                    char c = t[i];
                    if (c == '"') { i++; break; }
                    if (c == '\\' && i + 1 < t.Length)
                    {
                        char n = t[i + 1];
                        if (n == 'n') { sb.Append('\n'); i += 2; }
                        else if (n == 't') { sb.Append('\t'); i += 2; }
                        else if (n == '\\') { sb.Append('\\'); i += 2; }
                        else if (n == '"') { sb.Append('"'); i += 2; }
                        else if (n == '/') { sb.Append('/'); i += 2; }
                        else { sb.Append(n); i += 2; }
                    }
                    else { sb.Append(c); i++; }
                }
                return sb.ToString();
            }
            // number / literal
            int start = i;
            while (i < t.Length && (char.IsDigit(t[i]) || t[i] == '-' || t[i] == '+' || t[i] == '.' || t[i] == 'e' || t[i] == 'E')) i++;
            string num = t.Substring(start, i - start);
            if (num.Length > 0 && (char.IsDigit(num[0]) || num[0] == '-')) return num;
            return null;
        }
        static void SkipWs(string t, ref int i) { while (i < t.Length && Char.IsWhiteSpace(t[i])) i++; }

        // ───────────────────────── Process helpers ─────────────────────────
        public static bool AgentRunning()
        {
            Process[] ps = Process.GetProcessesByName("ph-agent");
            return ps.Length > 0;
        }

        public static void StartAgent()
        {
            if (ServiceInstalled())
            {
                RunCmd("sc", "start " + ServiceName);
                return;
            }
            if (AgentRunning()) { RunCmd("schtasks", "/Run /TN \"" + TaskName + "\""); return; }
            if (File.Exists(AgentPath))
            {
                Process.Start(new ProcessStartInfo
                {
                    FileName = AgentPath,
                    WorkingDirectory = AppDir,
                    UseShellExecute = true,
                    WindowStyle = ProcessWindowStyle.Hidden,
                });
            }
        }

        public static void StopAgent()
        {
            if (ServiceInstalled())
            {
                RunCmd("sc", "stop " + ServiceName);
                return;
            }
            try
            {
                Process[] ps = Process.GetProcessesByName("ph-agent");
                foreach (Process p in ps) { try { p.Kill(); } catch { } }
            }
            catch { }
        }

        public static bool ServiceInstalled()
        {
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Name FROM Win32_Service"))
                {
                    foreach (ManagementObject svc in searcher.Get())
                    {
                        object name = svc["Name"];
                        if (name != null && String.Equals(name.ToString(), ServiceName, StringComparison.OrdinalIgnoreCase))
                            return true;
                    }
                }
            }
            catch { }
            return false;
        }

        public static bool ServiceRunning()
        {
            string s = RunCmd("sc", "query " + ServiceName, false);
            return s != null && s.IndexOf("RUNNING", StringComparison.OrdinalIgnoreCase) >= 0;
        }

        public static string RunCmd(string file, string args)
        {
            return RunCmd(file, args, true);
        }

        public static string RunCmd(string file, string args, bool hidden)
        {
            try
            {
                ProcessStartInfo psi = new ProcessStartInfo(file, args);
                psi.UseShellExecute = false;
                psi.CreateNoWindow = hidden;
                psi.RedirectStandardOutput = true;
                psi.RedirectStandardError = true;
                psi.WindowStyle = hidden ? ProcessWindowStyle.Hidden : ProcessWindowStyle.Normal;
                Process p = Process.Start(psi);
                string o = p.StandardOutput.ReadToEnd();
                string e = p.StandardError.ReadToEnd();
                p.WaitForExit(20000);
                return o + e;
            }
            catch { return ""; }
        }

        // ───────────────────────── Service management ─────────────────────────
        public static void InstallService()
        {
            string bin = "\"" + ExePath + "\" --service";
            string sc = "create " + ServiceName
                + " start= auto"
                + " binPath= " + "\"" + bin.Replace("\"", "\\\"") + "\""
                + " DisplayName= \"Planet Host Game Node Agent\"";
            RunCmd("sc", sc);
            RunCmd("sc", "start " + ServiceName);
        }

        public static void UninstallService()
        {
            RunCmd("sc", "stop " + ServiceName);
            Thread.Sleep(1000);
            RunCmd("sc", "delete " + ServiceName);
        }

        public static void RegisterTask()
        {
            string bin = "\"" + AgentPath + "\"";
            string task = "/Create /F /TN \"" + TaskName + "\""
                + " /TR \"\\\"" + AgentPath + "\\\"\""
                + " /SC ONSTART /RL HIGHEST /RU SYSTEM";
            RunCmd("schtasks", task);
            try
            {
                using (RegistryKey rk = Registry.CurrentUser.OpenSubKey("Software\\Microsoft\\Windows\\CurrentVersion\\Run", true))
                {
                    if (rk != null) rk.SetValue(RunKey, "\"" + Application.ExecutablePath + "\"");
                }
            }
            catch { }
        }

        public static void UnregisterTask()
        {
            RunCmd("schtasks", "/Delete /F /TN \"" + TaskName + "\"");
            try
            {
                using (RegistryKey rk = Registry.CurrentUser.OpenSubKey("Software\\Microsoft\\Windows\\CurrentVersion\\Run", true))
                {
                    if (rk != null) rk.DeleteValue(RunKey, false);
                }
            }
            catch { }
        }

        // ───────────────────────── Shortcuts ─────────────────────────
        public static void CreateShortcut(string linkPath, string target, string wd, string desc, string icon)
        {
            try
            {
                Type t = Type.GetTypeFromProgID("WScript.Shell");
                object shell = Activator.CreateInstance(t);
                object sc = t.InvokeMember("CreateShortcut", System.Reflection.BindingFlags.InvokeMethod, null, shell,
                    new object[] { linkPath });
                t.InvokeMember("TargetPath", System.Reflection.BindingFlags.SetProperty, null, sc, new object[] { target });
                if (wd != null && wd.Length > 0)
                    t.InvokeMember("WorkingDirectory", System.Reflection.BindingFlags.SetProperty, null, sc, new object[] { wd });
                if (icon != null && icon.Length > 0)
                    t.InvokeMember("IconLocation", System.Reflection.BindingFlags.SetProperty, null, sc, new object[] { icon });
                t.InvokeMember("Description", System.Reflection.BindingFlags.SetProperty, null, sc, new object[] { desc });
                t.InvokeMember("Save", System.Reflection.BindingFlags.InvokeMethod, null, sc, null);
            }
            catch { }
        }

        public static void CreateAllShortcuts()
        {
            string link = Path.Combine(DesktopDir(), "Planet Host Agent.lnk");
            CreateShortcut(link, Application.ExecutablePath, AppDir, "Planet Host Game Node Agent control tray", Application.ExecutablePath + ",0");
            string un = Path.Combine(DesktopDir(), "Uninstall Planet Host Agent.lnk");
            CreateShortcut(un, Application.ExecutablePath, AppDir, "Uninstall the Planet Host Game Node Agent", Application.ExecutablePath + ",0");
            string sm = Path.Combine(StartMenuDir(), "Planet Host Agent.lnk");
            CreateShortcut(sm, Application.ExecutablePath, AppDir, "Planet Host Game Node Agent control tray", Application.ExecutablePath + ",0");
            string smu = Path.Combine(StartMenuDir(), "Uninstall Planet Host Agent.lnk");
            CreateShortcut(smu, Application.ExecutablePath, AppDir, "Uninstall the Planet Host Game Node Agent", Application.ExecutablePath + ",0");
            string startup = Path.Combine(StartupDir(), "Planet Host Agent Tray.lnk");
            CreateShortcut(startup, Application.ExecutablePath, AppDir, "Planet Host Game Node Agent tray (auto-start)", Application.ExecutablePath + ",0");
        }

        public static void RemoveShortcuts()
        {
            foreach (string p in new string[]
            {
                Path.Combine(DesktopDir(), "Planet Host Agent.lnk"),
                Path.Combine(DesktopDir(), "Uninstall Planet Host Agent.lnk"),
                Path.Combine(StartMenuDir(), "Planet Host Agent.lnk"),
                Path.Combine(StartMenuDir(), "Uninstall Planet Host Agent.lnk"),
                Path.Combine(StartupDir(), "Planet Host Agent Tray.lnk"),
            })
            {
                try { if (File.Exists(p)) File.Delete(p); } catch { }
            }
        }

        // ───────────────────────── Uninstall ─────────────────────────
        public static void SilentUninstall()
        {
            try { UninstallService(); } catch { }
            UnregisterTask();
            RemoveShortcuts();
            StopAgent();
            Thread.Sleep(800);
            // Schedule a delayed removal of the install folder, then exit. The
            // folder is deleted after this process closes.
            try
            {
                string dir = AppDir;
                string batch = Path.Combine(Path.GetTempPath(), "ph-agent-uninstall.bat");
                File.WriteAllText(batch, "@echo off\r\nping -n 4 127.0.0.1 >nul\r\n"
                    + "cd /d \"%SystemRoot%\\System32\"\r\n"
                    + "taskkill /F /IM ph-agent.exe >nul 2>&1\r\n"
                    + "rd /s /q \"" + dir + "\" >nul 2>&1\r\n"
                    + "del /f /q \"" + batch + "\" >nul 2>&1\r\n");
                Process.Start(new ProcessStartInfo("cmd.exe", "/c \"" + batch + "\"") { UseShellExecute = true, WindowStyle = ProcessWindowStyle.Hidden });
            }
            catch { }
        }
    }

    // ───────────────────────── Windows Service host ─────────────────────────
    public class AgentService : ServiceBase
    {
        private Process _proc;

        public AgentService()
        {
            ServiceName = Program.ServiceName;
            CanStop = true;
            CanShutdown = true;
            AutoLog = true;
        }

        // In service mode we spawn ph-agent.exe directly. Program.StartAgent()
        // would call `sc start` on ourselves (no-op recursion), so bypass it.
        protected override void OnStart(string[] args)
        {
            try
            {
                _proc = new Process();
                _proc.StartInfo.FileName = Program.AgentPath;
                _proc.StartInfo.WorkingDirectory = Program.AppDir;
                _proc.StartInfo.UseShellExecute = false;
                _proc.StartInfo.CreateNoWindow = true;
                _proc.Start();
            }
            catch { }
            base.OnStart(args);
        }

        protected override void OnStop()
        {
            try
            {
                Process[] ps = Process.GetProcessesByName("ph-agent");
                foreach (Process p in ps) { try { p.Kill(); } catch { } }
            }
            catch { }
            base.OnStop();
        }

        protected override void OnShutdown()
        {
            OnStop();
        }
    }

    // ───────────────────────── Tray application ─────────────────────────
    public class TrayApp : ApplicationContext
    {
        private NotifyIcon _tray;
        private System.Windows.Forms.Timer _timer;
        private ContextMenuStrip _menu;
        private ToolStripMenuItem _miStatus, _miStart, _miStop, _miRestart;
        private ToolStripMenuItem _miServiceStatus, _miServiceInstall, _miServiceUninstall;
        private ControlWindow _win;

        public TrayApp()
        {
            _tray = new NotifyIcon();
            _tray.Icon = SystemIcons.Application;
            _tray.Text = "Planet Host Game Node Agent";
            _tray.Visible = true;
            BuildMenu();
            _tray.ContextMenuStrip = _menu;
            _tray.DoubleClick += delegate { ShowWindow(); };

            Program.CreateAllShortcuts();

            _timer = new System.Windows.Forms.Timer();
            _timer.Interval = 5000;
            _timer.Tick += delegate { RefreshStatus(); };
            _timer.Start();
            RefreshStatus();
        }

        void BuildMenu()
        {
            _menu = new ContextMenuStrip();

            _miStatus = new ToolStripMenuItem("Status: …");
            _miStatus.Enabled = false;

            _miStart = new ToolStripMenuItem("Start Agent", null, delegate { Program.StartAgent(); RefreshStatus(); });
            _miStop = new ToolStripMenuItem("Stop Agent", null, delegate { Program.StopAgent(); RefreshStatus(); });
            _miRestart = new ToolStripMenuItem("Restart Agent", null, delegate { Program.StopAgent(); Thread.Sleep(700); Program.StartAgent(); RefreshStatus(); });

            ToolStripMenuItem open = new ToolStripMenuItem("Open Agent Folder", null, delegate
            {
                try { Process.Start("explorer.exe", "/select,\"" + Program.AgentPath + "\""); } catch { }
            });
            ToolStripMenuItem cfg = new ToolStripMenuItem("Edit Config", null, delegate
            {
                try { Process.Start("notepad.exe", "\"" + Program.ConfigPath + "\""); } catch { }
            });
            ToolStripMenuItem locs = new ToolStripMenuItem("Game Locations (Drives)…", null, delegate { ShowWindow(); });

            ToolStripMenuItem service = new ToolStripMenuItem("Run as a Windows Service",
                null, delegate { Program.InstallService(); RefreshStatus(); });
            _miServiceInstall = service;
            _miServiceUninstall = new ToolStripMenuItem("Uninstall Service",
                null, delegate { Program.UninstallService(); RefreshStatus(); });
            _miServiceStatus = new ToolStripMenuItem("Service: —");
            _miServiceStatus.Enabled = false;

            ToolStripMenuItem panel = new ToolStripMenuItem("Open Panel / Nodes", null, delegate
            {
                Program.Config c = Program.LoadConfig();
                try { Process.Start("explorer.exe", c.panel_url); } catch { }
            });

            ToolStripMenuItem cmp = new ToolStripMenuItem("Control Panel…", null, delegate { ShowWindow(); });

            ToolStripMenuItem uninstall = new ToolStripMenuItem("Uninstall Agent…", null, delegate
            {
                DialogResult dr = MessageBox.Show(
                    "This stops the agent, removes the Windows service + auto-start,\n"
                    + "deletes shortcuts, and removes the installed files.\n\n"
                    + "Continue?", "Uninstall Planet Host Agent",
                    MessageBoxButtons.YesNo, MessageBoxIcon.Warning);
                if (dr != DialogResult.Yes) return;
                Program.SilentUninstall();
                ExitThread();
            });

            _menu.Items.Add(_miStatus);
            _menu.Items.Add(new ToolStripSeparator());
            _menu.Items.Add(_miStart);
            _menu.Items.Add(_miStop);
            _menu.Items.Add(_miRestart);
            _menu.Items.Add(new ToolStripSeparator());
            _menu.Items.Add(open);
            _menu.Items.Add(cfg);
            _menu.Items.Add(locs);
            _menu.Items.Add(new ToolStripSeparator());
            _menu.Items.Add(_miServiceStatus);
            _menu.Items.Add(_miServiceInstall);
            _menu.Items.Add(_miServiceUninstall);
            _menu.Items.Add(new ToolStripSeparator());
            _menu.Items.Add(panel);
            _menu.Items.Add(cmp);
            _menu.Items.Add(new ToolStripSeparator());
            _menu.Items.Add(uninstall);
        }

        void RefreshStatus()
        {
            bool svcInstalled = Program.ServiceInstalled();
            bool svcRunning = svcInstalled && Program.ServiceRunning();
            bool procRunning = Program.AgentRunning();
            bool online = false;
            try
            {
                if (File.Exists(Program.StatusPath))
                {
                    DateTime last = File.GetLastWriteTime(Program.StatusPath);
                    online = last > DateTime.Now.AddSeconds(-35);
                }
            }
            catch { }

            string body = "";
            if (svcInstalled) body += "Service: " + (svcRunning ? "RUNNING" : "STOPPED") + "\n";
            bool up = svcRunning || procRunning;
            body += "Process: " + (procRunning ? "RUNNING" : "stopped") + "\n";
            body += "Panel link: " + (online ? "ONLINE" : "offline");

            Program.Config c = Program.LoadConfig();
            if (c.panel_url.Length > 0) body += " (" + c.panel_url + ")";

            _miStatus.Text = "Status: " + (up ? "RUNNING" : "stopped") + (online ? " • ONLINE" : "");
            _tray.Text = "Planet Host Game Node Agent — " + (up ? "RUNNING" : "stopped") + (online ? " • ONLINE" : "");

            _miServiceStatus.Text = "Service: " + (svcInstalled ? (svcRunning ? "RUNNING" : "STOPPED") : "not installed");
            _miServiceInstall.Enabled = !svcInstalled;
            _miServiceUninstall.Enabled = svcInstalled;
            _miStart.Enabled = !up;
            _miStop.Enabled = up;
            _miRestart.Enabled = up;

            if (_win != null && _win.Visible) _win.SetDetails(body, up, online);
        }

        void ShowWindow()
        {
            if (_win == null || _win.IsDisposed)
            {
                _win = new ControlWindow(this);
            }
            _win.Show();
            _win.BringToFront();
            _win.Activate();
        }

        protected override void ExitThreadCore()
        {
            if (_tray != null) { _tray.Visible = false; _tray.Dispose(); }
            if (_timer != null) _timer.Dispose();
            base.ExitThreadCore();
        }
    }

    // ───────────────────────── Control window ─────────────────────────
    public class ControlWindow : Form
    {
        private TrayApp _owner;
        private Label _lblStatus;
        private Button _btnStart, _btnStop, _btnRestart, _btnOpen, _btnService, _btnUninstallSvc, _btnAddLoc, _btnRemoveLoc, _btnUninstall;
        private ListBox _listLocs;
        private System.Windows.Forms.Timer _refresh;

        public ControlWindow(TrayApp owner)
        {
            _owner = owner;
            Text = "Planet Host — Game Node Agent";
            Font = new Font("Segoe UI", 9.5f);
            ClientSize = new Size(560, 480);
            MinimumSize = new Size(560, 480);
            MaximizeBox = false;
            FormBorderStyle = FormBorderStyle.FixedSingle;
            BackColor = Color.FromArgb(243, 245, 249);
            StartPosition = FormStartPosition.CenterScreen;

            var h = new Label
            {
                Text = "Planet Host Game Node Agent",
                Font = new Font("Segoe UI", 14f, FontStyle.Bold),
                ForeColor = Color.FromArgb(13, 110, 253),
                Location = new Point(22, 10), AutoSize = true, BackColor = BackColor,
            };
            Controls.Add(h);

            _lblStatus = new Label
            {
                Location = new Point(22, 46), Size = new Size(ClientSize.Width - 44, 70), BackColor = Color.Transparent,
                ForeColor = Color.FromArgb(50, 50, 50),
            };
            Controls.Add(_lblStatus);

            _btnStart = Btn("▶ Start", 22, 128, Color.FromArgb(40, 167, 69));
            _btnStart.Click += delegate { Program.StartAgent(); RefreshStatus(); };
            _btnStop = Btn("■ Stop", 128, 128, Color.FromArgb(220, 53, 69));
            _btnStop.Click += delegate { Program.StopAgent(); RefreshStatus(); };
            _btnRestart = Btn("↻ Restart", 234, 128, Color.FromArgb(13, 110, 253));
            _btnRestart.Click += delegate { Program.StopAgent(); Thread.Sleep(700); Program.StartAgent(); RefreshStatus(); };
            _btnOpen = Btn("Folder", 340, 128, Color.FromArgb(90, 90, 90));
            _btnOpen.Click += delegate { try { Process.Start("explorer.exe", "/select,\"" + Program.AgentPath + "\""); } catch { } };
            Controls.Add(_btnStart); Controls.Add(_btnStop); Controls.Add(_btnRestart); Controls.Add(_btnOpen);

            var lblLocs = new Label { Text = "Install locations (drives where game servers are stored)", Location = new Point(22, 182), AutoSize = true, ForeColor = Color.FromArgb(70, 70, 70), BackColor = BackColor };
            Controls.Add(lblLocs);
            _listLocs = new ListBox { Location = new Point(22, 208), Size = new Size(ClientSize.Width - 44, 130) };
            Controls.Add(_listLocs);

            _btnAddLoc = Btn("＋ Add Drive…", 22, 352, Color.FromArgb(13, 110, 253));
            _btnAddLoc.Click += delegate { AddLocation(); };
            _btnRemoveLoc = Btn("Remove", 130, 352, Color.FromArgb(220, 53, 69));
            _btnRemoveLoc.Click += delegate { RemoveLocation(); };
            _btnService = Btn("Install Service", 234, 352, Color.FromArgb(40, 167, 69));
            _btnService.Click += delegate { Program.InstallService(); RefreshStatus(); };
            _btnUninstallSvc = Btn("Uninstall Service", 340, 352, Color.FromArgb(180, 0, 0));
            _btnUninstallSvc.Click += delegate { Program.UninstallService(); RefreshStatus(); };
            Controls.Add(_btnAddLoc); Controls.Add(_btnRemoveLoc); Controls.Add(_btnService); Controls.Add(_btnUninstallSvc);

            var lblNote = new Label
            {
                Text = "The agent connects OUT to the panel — no inbound ports needed. Running as a\nservice keeps it online 24/7 (even with no user logged in).",
                Location = new Point(22, 392), AutoSize = true, ForeColor = Color.FromArgb(100, 100, 100), BackColor = BackColor,
            };
            Controls.Add(lblNote);

            _btnUninstall = new Button
            {
                Text = "Uninstall Agent…", Size = new Size(150, 34), FlatStyle = FlatStyle.Flat,
                BackColor = Color.FromArgb(220, 53, 69), ForeColor = Color.White,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Location = new Point(ClientSize.Width - 172, 440),
            };
            _btnUninstall.Click += delegate
            {
                DialogResult dr = MessageBox.Show("Stop agent, remove service/auto-start/shortcuts and delete files?",
                    "Uninstall Planet Host Agent", MessageBoxButtons.YesNo, MessageBoxIcon.Warning);
                if (dr != DialogResult.Yes) return;
                Program.SilentUninstall();
                _owner.ExitThread();
            };
            Controls.Add(_btnUninstall);

            _refresh = new System.Windows.Forms.Timer();
            _refresh.Interval = 3000;
            _refresh.Tick += delegate { RefreshStatus(); };
            _refresh.Start();

            FormClosing += delegate { _refresh.Stop(); };
            RefreshStatus();
        }

        Button Btn(string text, int x, int y, Color color)
        {
            return new Button
            {
                Text = text, Location = new Point(x, y), Size = new Size(104, 30), FlatStyle = FlatStyle.Flat,
                BackColor = color, ForeColor = Color.White, Font = new Font("Segoe UI", 9f, FontStyle.Bold),
            };
        }

        public void SetDetails(string body, bool up, bool online)
        {
            _lblStatus.Text = body;
            bool svc = Program.ServiceInstalled();
            _btnStart.Enabled = !up;
            _btnStop.Enabled = up;
            _btnRestart.Enabled = up;
            _btnService.Enabled = !svc;
            _btnUninstallSvc.Enabled = svc;
        }

        void RefreshStatus()
        {
            SetDetails("Service: " + (Program.ServiceInstalled() ? (Program.ServiceRunning() ? "RUNNING" : "STOPPED") : "not installed") + "\n"
                + "Process: " + (Program.AgentRunning() ? "RUNNING" : "stopped"),
                Program.AgentRunning() || Program.ServiceRunning(), false);

            Program.Config c = Program.LoadConfig();
            int sel = _listLocs.SelectedIndex;
            _listLocs.Items.Clear();
            if (c.locations.Count > 0) foreach (string l in c.locations) _listLocs.Items.Add(Exists(l) ? l : l + "  [missing]");
            if (sel >= 0 && sel < _listLocs.Items.Count) _listLocs.SelectedIndex = sel;
        }

        bool Exists(string path)
        {
            try
            {
                if (path.Length > 2 && path[1] == ':') return Directory.Exists(path);
                return Directory.Exists(path);
            }
            catch { return false; }
        }

        void AddLocation()
        {
            FolderBrowserDialog dlg = new FolderBrowserDialog
            {
                Description = "Choose the drive/folder where game servers are stored (e.g. D:\\Games)",
            };
            if (dlg.ShowDialog(this) != DialogResult.OK) return;
            Program.Config c = Program.LoadConfig();
            if (!c.loaded) { MessageBox.Show("Cannot read config.\n" + c.loadError); return; }
            string p = dlg.SelectedPath.Trim();
            try { p = Path.GetFullPath(p); } catch { }
            if (c.locations.IndexOf(p) >= 0) { MessageBox.Show("That location is already listed."); return; }
            c.locations.Add(p);
            if (c.base_dir.Length == 0) c.base_dir = p;
            Program.SaveConfig(c);
            Program.StopAgent();
            Thread.Sleep(400);
            Program.StartAgent();
            RefreshStatus();
        }

        void RemoveLocation()
        {
            int idx = _listLocs.SelectedIndex;
            if (idx < 0) { MessageBox.Show("Select a location to remove."); return; }
            Program.Config c = Program.LoadConfig();
            if (idx < 0 || idx >= c.locations.Count) return;
            string removed = c.locations[idx];
            DialogResult dr = MessageBox.Show("Remove " + removed + " from the agent's install locations?", "Remove Location",
                MessageBoxButtons.YesNo, MessageBoxIcon.Question);
            if (dr != DialogResult.Yes) return;
            c.locations.RemoveAt(idx);
            if (c.base_dir == removed && c.locations.Count > 0) c.base_dir = c.locations[0];
            Program.SaveConfig(c);
            Program.StopAgent();
            Thread.Sleep(400);
            Program.StartAgent();
            RefreshStatus();
        }
    }
}