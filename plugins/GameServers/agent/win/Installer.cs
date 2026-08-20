// Planet Hosts Game Node Agent — Windows Installer (GUI)
// Single-file installer: embeds ph-agent.exe + this UI. User fills in:
//   • Panel URL           (e.g. https://planet-hosts.com)
//   • Node token          (generated in Admin → Games → Game Nodes)
//   • Agent install folder
//   • Game install folder
//   • Steam login (OPTIONAL — only needed when installing games that must
//     be purchased on Steam; free/downloadable games use anonymous)
// The panel's own settings (/admin/games/settings) are NEVER requested or
// shipped — the node keeps its own generic config and talks to the panel.
//
// Build (on Windows, .NET Framework 4.x csc):
// csc.exe /nologo /t:winexe /out:ph-agent-installer.exe /main:Installer.Program
        //     /win32manifest:installer.manifest
        //     /resource:<abs path>\ph-agent.exe,embedded_agent_exe
        //     /r:System.Windows.Forms.dll /r:System.Drawing.dll
        //     Installer.cs
using System;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Text;
using System.Windows.Forms;
using System.Diagnostics;
using System.Reflection;

namespace Installer
{
    public class Program : Form
    {
        private TextBox      txtPanel, txtToken, txtSteamUser, txtSteamPass;
        private TextBox      txtAgentDir, txtGamesDir;
        private Button       btnAgentDir, btnGamesDir, btnTest, btnInstall;
        private CheckBox     chkSteam;
        private Label        lblStatus;
        private Panel        pnlSteam;

        private const string RES_AGENT = "embedded_agent_exe";
        private const string TASK_NAME = "PlanetHostsAgent";

        [STAThread]
        public static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new Program());
        }

        public Program()
        {
            Text = "Planet Hosts — Game Node Agent Installer";
            Font = new Font("Segoe UI", 9.5f);
            ClientSize = new Size(560, 470);
            MinimumSize = new Size(560, 470);
            MaximizeBox = false;
            FormBorderStyle = FormBorderStyle.FixedSingle;
            BackColor = Color.FromArgb(243, 245, 249);
            StartPosition = FormStartPosition.CenterScreen;

            var y = 18;
            AddHeader("Planet Hosts Game Node Agent", 18);

            y = AddLabel("Panel URL (your hosting panel address)", y);
            txtPanel = AddTextBox("https://planet-hosts.com", y); y += 34;

            y = AddLabel("Node token (Admin → Games → Game Nodes → generate one)", y);
            txtToken = AddTextBox("", y); txtToken.UseSystemPasswordChar = false; y += 34;

            y = AddLabel("Agent install folder", y);
            txtAgentDir = AddDirRow(y, out btnAgentDir, ProgramFilesPath()); y += 40;

            y = AddLabel("Game install folder", y);
            txtGamesDir = AddDirRow(y, out btnGamesDir, "C:\\PlanetHostsGames"); y += 40;

            y += 6;
            chkSteam = new CheckBox
            {
                Text = "I will install games that must be purchased on Steam (e.g. ARK, Valheim)",
                AutoSize = true,
                Location = new Point(28, y),
                Checked = false,
                BackColor = BackColor,
                ForeColor = Color.FromArgb(60, 60, 60),
            };
            chkSteam.CheckedChanged += delegate { pnlSteam.Visible = chkSteam.Checked; };
            Controls.Add(chkSteam); y += 30;

            pnlSteam = new Panel { Location = new Point(20, y), Size = new Size(ClientSize.Width - 40, 96), Visible = false, BackColor = Color.FromArgb(255, 250, 235) };
            var lblSteam = new Label
            {
                Text = "Enter YOUR Steam account (used only on this machine for those games).",
                Location = new Point(12, 10), AutoSize = true, BackColor = Color.Transparent,
                ForeColor = Color.FromArgb(120, 90, 0),
            };
            txtSteamUser = new TextBox { Location = new Point(12, 34), Width = 230 };
            txtSteamUser.TextChanged += delegate { };
            txtSteamPass = new TextBox { Location = new Point(258, 34), Width = 230, UseSystemPasswordChar = true };
            pnlSteam.Controls.Add(lblSteam);
            pnlSteam.Controls.Add(txtSteamUser);
            pnlSteam.Controls.Add(txtSteamPass);
            Controls.Add(pnlSteam);
            y += pnlSteam.Height + 4;

            btnTest = AddButton("Test Connection", y, 0, new Point(28, 300));
            btnTest.Click += delegate { TestConnection(); };

            btnInstall = new Button
            {
                Text = "Install Agent",
                Size = new Size(140, 34),
                BackColor = Color.FromArgb(13, 110, 253),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9.5f, FontStyle.Bold),
                Location = new Point(ClientSize.Width - 168, 300),
            };
            btnInstall.Click += delegate { Install(); };
            Controls.Add(btnInstall);

            lblStatus = new Label
            {
                Location = new Point(28, 350),
                Size = new Size(ClientSize.Width - 56, 90),
                AutoSize = false,
                ForeColor = Color.FromArgb(50, 50, 50),
            };
            Controls.Add(lblStatus);

            ShiftBottom();
        }

        private void ShiftBottom()
        {
            var sh = btnInstall.Bottom + 6;
            if (sh > ClientSize.Height)
            {
                ClientSize = new Size(ClientSize.Width, ClientSize.Height + sh - ClientSize.Height + 24);
            }
        }

        private string ProgramFilesPath()
        {
            try { return Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles), "PlanetHostsAgent"); }
            catch { return "C:\\Program Files\\PlanetHostsAgent"; }
        }

        // ---- UI helpers ----
        private void AddHeader(string text, int h)
        {
            var l = new Label
            {
                Text = text,
                Font = new Font("Segoe UI", 13f, FontStyle.Bold),
                ForeColor = Color.FromArgb(13, 110, 253),
                Location = new Point(24, 8),
                AutoSize = true,
                BackColor = BackColor,
            };
            Controls.Add(l);
        }

        private int AddLabel(string text, int y)
        {
            var l = new Label
            { Text = text, Location = new Point(28, y), AutoSize = true, BackColor = BackColor, ForeColor = Color.FromArgb(70, 70, 70) };
            Controls.Add(l);
            return y + 22;
        }

        private TextBox AddTextBox(string def, int y)
        {
            var t = new TextBox { Text = def, Location = new Point(28, y), Width = ClientSize.Width - 56 };
            Controls.Add(t);
            return t;
        }

        private TextBox AddDirRow(int y, out Button browse, string def)
        {
            var t = new TextBox { Text = def, Location = new Point(28, y), Width = ClientSize.Width - 120 };
            browse = new Button { Text = "Browse…", Location = new Point(ClientSize.Width - 86, y - 2), Size = new Size(70, 27) };
            browse.Click += delegate
            {
                var dlg = new FolderBrowserDialog { Description = "Choose folder", SelectedPath = t.Text };
                if (dlg.ShowDialog() == DialogResult.OK) t.Text = dlg.SelectedPath;
            };
            Controls.Add(t);
            Controls.Add(browse);
            return t;
        }

        private Button AddButton(string text, int _, int __, Point loc)
        {
            var b = new Button
            {
                Text = text,
                Location = loc,
                Size = new Size(110, 34),
                BackColor = Color.FromArgb(40, 167, 69),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
            };
            Controls.Add(b);
            return b;
        }

        private void SetStatus(string msg, bool ok)
        {
            lblStatus.Text = msg;
            lblStatus.ForeColor = ok ? Color.FromArgb(0, 128, 0) : Color.FromArgb(180, 0, 0);
        }

        private bool ValidateFields()
        {
            var panel = (txtPanel.Text ?? "").Trim();
            var token = (txtToken.Text ?? "").Trim();
            if (panel.Length < 8 || !panel.StartsWith("http")) { SetStatus("Enter a valid panel URL starting with http(s).", false); return false; }
            if (token.Length < 10) { SetStatus("Enter the node token generated on the panel.", false); return false; }
            return true;
        }

        private void TestConnection()
        {
            if (!ValidateFields()) return;
            var panel = txtPanel.Text.Trim().TrimEnd('/');
            var token = txtToken.Text.Trim();
            SetStatus("Testing connection…", true);
            try
            {
                var client = new WebClient();
                client.Encoding = Encoding.UTF8;
                var body = client.DownloadString(panel + "/api/agent/commands?token=" + Uri.EscapeDataString(token));
                if (body.IndexOf("Unauthorized", StringComparison.OrdinalIgnoreCase) >= 0 || body.IndexOf("\"error\"", StringComparison.OrdinalIgnoreCase) >= 0)
                {
                    SetStatus("Connection OK but TOKEN REJECTED (401). Check the token on the panel.", false);
                }
                else
                {
                    SetStatus("Connection OK — token accepted. You can now Install Agent.", true);
                }
            }
            catch (Exception ex)
            {
                SetStatus("Cannot reach panel: " + ex.Message.Split('\n')[0], false);
            }
        }

        private void Install()
        {
            if (!ValidateFields()) return;
            var panel  = txtPanel.Text.Trim().TrimEnd('/');
            var token  = txtToken.Text.Trim();
            var dir    = txtAgentDir.Text.Trim();
            var gdir   = txtGamesDir.Text.Trim();
            var sUser  = chkSteam.Checked ? txtSteamUser.Text.Trim() : "";
            var sPass  = chkSteam.Checked ? txtSteamPass.Text : "";

            if (!chkSteam.Checked) { sUser = "anonymous"; sPass = ""; }
            if (chkSteam.Checked && sUser.Length == 0) { SetStatus("Steam is checked but no username was entered.", false); return; }

            try { Directory.CreateDirectory(dir); Directory.CreateDirectory(gdir); }
            catch (Exception ex) { SetStatus("Cannot create folders: " + ex.Message, false); return; }

            var exePath = Path.Combine(dir, "ph-agent.exe");
            SetStatus("Extracting agent…", true);
            try
            {
                using (var src = Assembly.GetExecutingAssembly().GetManifestResourceStream(RES_AGENT))
                {
                    if (src == null) { SetStatus("Agent binary missing from installer.", false); return; }
                    using (var dst = File.Create(exePath)) { src.CopyTo(dst); }
                }
            }
            catch (Exception ex) { SetStatus("Could not write agent: " + ex.Message, false); return; }

            var cfg = "{"
                + "\"panel_url\":\"" + JsonStr(panel) + "\","
                + "\"node_token\":\"" + JsonStr(token) + "\","
                + "\"base_dir\":\"" + JsonStr(gdir) + "\","
                + "\"poll_interval_ms\":10000,"
                + "\"steamcmd\":\"steamcmd.exe\","
                + "\"steam_user\":\"" + JsonStr(sUser) + "\","
                + "\"steam_pass\":\"" + JsonStr(sPass) + "\""
                + "}";
            try { File.WriteAllText(Path.Combine(dir, "agent-config.json"), cfg); }
            catch (Exception ex) { SetStatus("Cannot write config: " + ex.Message, false); return; }

            // Register scheduled task (auto-start at boot, SYSTEM)
            var ok = RegisterTask(dir, exePath);
            if (ok)
            {
                try { Process.Start(new ProcessStartInfo { FileName = exePath, WorkingDirectory = dir, UseShellExecute = true, WindowStyle = ProcessWindowStyle.Hidden }); }
                catch { }
                SetStatus("Installed and started. Watch Admin → Games → Game Nodes — node should go ONLINE within ~10s.", true);
            }
            else
            {
                SetStatus("Installed files, but could not register auto-start (run as Administrator). Start manually: " + exePath, false);
            }
        }

        private bool RegisterTask(string dir, string exePath)
        {
            try
            {
                var psi = new ProcessStartInfo("schtasks.exe");
                psi.Arguments =
                    "/Create /F /TN \"" + TASK_NAME + "\" "
                    + "/TR \"\\\"" + exePath + "\\\"\" "
                    + "/SC ONSTART /RL HIGHEST /RU SYSTEM";
                psi.UseShellExecute = false;
                psi.CreateNoWindow = true;
                var p = Process.Start(psi);
                p.WaitForExit(15000);
                return p.ExitCode == 0 || p.ExitCode == 1;
            }
            catch { return false; }
        }

        private static string JsonStr(string s)
        {
            return (s ?? "").Replace("\\", "\\\\").Replace("\"", "\\\"");
        }
    }
}