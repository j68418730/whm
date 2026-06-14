<?php

namespace User\Controllers;

use Core\Controller;

class AppsController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        return $user;
    }

    public function node()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $apps = $uid ? ($this->db->table('node_apps')->where('user_id', $uid)->get() ?: []) : [];
        $nodeVer = trim(shell_exec('node --version 2>/dev/null') ?: 'Not installed');
        $npmVer = trim(shell_exec('npm --version 2>/dev/null') ?: 'Not installed');
        return $this->view('user.apps_node', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Node.js Apps', 'apps' => $apps, 'nodeVer' => $nodeVer, 'npmVer' => $npmVer]);
    }

    public function nodeCreate()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $port = (int)$this->request->post('port', 3000);
        $this->db->table('node_apps')->insertGetId([
            'user_id' => $uid,
            'name' => $this->request->post('name', ''),
            'domain' => $this->request->post('domain', ''),
            'port' => $port,
            'entry_point' => $this->request->post('entry_point', 'app.js'),
            'status' => 'stopped',
        ]);
        $homeDir = '/home/' . ($this->hostingUser->username ?? '');
        $appDir = "$homeDir/nodejs/" . $this->request->post('name', 'app');
        @mkdir($appDir, 0755, true);
        file_put_contents("$appDir/app.js", "const http = require('http');\nconst port = $port;\nhttp.createServer((req, res) => {\n  res.writeHead(200);\n  res.end('Hello from Node.js');\n}).listen(port, () => console.log('Running on port ' + port));\n");
        $_SESSION['success'] = 'Node.js app created.';
        $this->response->redirect('/user/apps/node');
    }

    public function nodeStart($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('node_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $homeDir = '/home/' . ($this->hostingUser->username ?? '');
            $appDir = "$homeDir/nodejs/" . $app->name;
            shell_exec("cd " . escapeshellarg($appDir) . " && nohup node " . escapeshellarg($app->entry_point) . " > app.log 2>&1 &");
            $this->db->table('node_apps')->where('id', $id)->update(['status' => 'running']);
        }
        $this->response->redirect('/user/apps/node');
    }

    public function nodeStop($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('node_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $port = $app->port;
            shell_exec("fuser -k {$port}/tcp 2>/dev/null");
            $this->db->table('node_apps')->where('id', $id)->update(['status' => 'stopped']);
        }
        $this->response->redirect('/user/apps/node');
    }

    public function nodeDelete($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('node_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $this->nodeStop($id);
            $homeDir = '/home/' . ($this->hostingUser->username ?? '');
            $appDir = "$homeDir/nodejs/" . $app->name;
            shell_exec("rm -rf " . escapeshellarg($appDir));
            $this->db->table('node_apps')->where('id', $id)->delete();
        }
        $this->response->redirect('/user/apps/node');
    }

    public function python()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $apps = $uid ? ($this->db->table('python_apps')->where('user_id', $uid)->get() ?: []) : [];
        $pyVer = trim(shell_exec('python3 --version 2>/dev/null') ?: 'Not installed');
        return $this->view('user.apps_python', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Python Apps', 'apps' => $apps, 'pyVer' => $pyVer]);
    }

    public function pythonCreate()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $port = (int)$this->request->post('port', 8000);
        $fw = $this->request->post('framework', 'flask');
        $this->db->table('python_apps')->insertGetId([
            'user_id' => $uid,
            'name' => $this->request->post('name', ''),
            'domain' => $this->request->post('domain', ''),
            'port' => $port,
            'entry_point' => $this->request->post('entry_point', 'app.py'),
            'framework' => $fw,
            'status' => 'stopped',
        ]);
        $homeDir = '/home/' . ($this->hostingUser->username ?? '');
        $appDir = "$homeDir/python/" . $this->request->post('name', 'app');
        @mkdir($appDir, 0755, true);
        $code = $fw === 'django'
            ? "import django\nprint('Django app ready')\n"
            : "from flask import Flask\napp = Flask(__name__)\n@app.route('/')\ndef home():\n    return 'Hello from Python'\nif __name__ == '__main__':\n    app.run(port=$port)\n";
        file_put_contents("$appDir/app.py", $code);
        $_SESSION['success'] = 'Python app created.';
        $this->response->redirect('/user/apps/python');
    }

    public function pythonStart($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('python_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $homeDir = '/home/' . ($this->hostingUser->username ?? '');
            $appDir = "$homeDir/python/" . $app->name;
            shell_exec("cd " . escapeshellarg($appDir) . " && nohup python3 " . escapeshellarg($app->entry_point) . " > app.log 2>&1 &");
            $this->db->table('python_apps')->where('id', $id)->update(['status' => 'running']);
        }
        $this->response->redirect('/user/apps/python');
    }

    public function pythonStop($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('python_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $port = $app->port;
            shell_exec("fuser -k {$port}/tcp 2>/dev/null");
            $this->db->table('python_apps')->where('id', $id)->update(['status' => 'stopped']);
        }
        $this->response->redirect('/user/apps/python');
    }

    public function pythonDelete($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $app = $this->db->table('python_apps')->where('id', $id)->where('user_id', $uid)->first();
        if ($app) {
            $this->pythonStop($id);
            $homeDir = '/home/' . ($this->hostingUser->username ?? '');
            $appDir = "$homeDir/python/" . $app->name;
            shell_exec("rm -rf " . escapeshellarg($appDir));
            $this->db->table('python_apps')->where('id', $id)->delete();
        }
        $this->response->redirect('/user/apps/python');
    }
}
