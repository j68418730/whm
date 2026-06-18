<?php
namespace Core;

class SshJail
{
    const JAIL_DIR = '/jail';
    const JK_LSH = '/usr/sbin/jk_lsh';

    public static function applySshAccess($username, $access)
    {
        $homeDir = "/home/{$username}";
        if (!is_dir($homeDir)) return false;

        switch ($access) {
            case 'full':
                self::setShell($username, '/bin/bash');
                self::removeJail($username);
                self::ensureSshDir($homeDir);
                break;
            case 'jailed':
                self::setShell($username, self::JK_LSH);
                self::applyJail($username);
                self::ensureSshDir($homeDir);
                break;
            case 'sftp':
                self::setShell($username, '/usr/sbin/nologin');
                self::removeJail($username);
                self::ensureSshDir($homeDir);
                break;
            case 'none':
                self::setShell($username, '/usr/sbin/nologin');
                self::removeJail($username);
                break;
        }
        return true;
    }

    public static function setSshKey($username, $key)
    {
        $homeDir = "/home/{$username}";
        $sshDir = "{$homeDir}/.ssh";
        if (!is_dir($sshDir)) mkdir($sshDir, 0700, true);
        file_put_contents("{$sshDir}/authorized_keys", $key . "\n");
        chmod("{$sshDir}/authorized_keys", 0600);
        chown($sshDir, $username);
        chown("{$sshDir}/authorized_keys", $username);
        return true;
    }

    public static function deleteSshKey($username)
    {
        $file = "/home/{$username}/.ssh/authorized_keys";
        if (file_exists($file)) {
            file_put_contents($file, '');
            chmod($file, 0600);
        }
        return true;
    }

    public static function generateKeyPair($username)
    {
        $homeDir = "/home/{$username}";
        $sshDir = "{$homeDir}/.ssh";
        if (!is_dir($sshDir)) mkdir($sshDir, 0700, true);
        exec(escapeshellcmd("ssh-keygen -t rsa -b 4096 -f {$sshDir}/id_rsa -N '' -C '{$username}@planethosts'") . " 2>/dev/null", $out, $code);
        if ($code === 0) {
            $pubKey = file_get_contents("{$sshDir}/id_rsa.pub");
            file_put_contents("{$sshDir}/authorized_keys", $pubKey);
            chmod("{$sshDir}/id_rsa", 0600);
            chmod("{$sshDir}/id_rsa.pub", 0600);
            chmod("{$sshDir}/authorized_keys", 0600);
            chown_r($sshDir, $username);
            return $pubKey;
        }
        return null;
    }

    private static function setShell($username, $shell)
    {
        exec(escapeshellcmd("chsh -s {$shell} {$username}") . " 2>/dev/null");
    }

    private static function applyJail($username)
    {
        exec(escapeshellcmd("jk_jailuser -j " . self::JAIL_DIR . " {$username}") . " 2>/dev/null", $out, $code);
        // Create home inside jail
        $jailHome = self::JAIL_DIR . "/home/{$username}";
        if (!is_dir($jailHome)) {
            mkdir($jailHome, 0755, true);
            exec("chown {$username}:{$username} {$jailHome} 2>/dev/null");
        }
    }

    private static function removeJail($username)
    {
        // Remove jail symlinks - jk_jailuser doesn't have an unjail command
        // Just ensure the user's shell is not jk_lsh
        $jailHome = self::JAIL_DIR . "/home/{$username}";
        if (is_dir($jailHome)) {
            exec("rm -rf {$jailHome} 2>/dev/null");
        }
    }

    private static function ensureSshDir($homeDir)
    {
        $sshDir = "{$homeDir}/.ssh";
        if (!is_dir($sshDir)) {
            mkdir($sshDir, 0700, true);
        }
    }

    public static function getStatus($username)
    {
        $shell = exec("grep '^{$username}:' /etc/passwd | cut -d: -f7");
        $hasKey = file_exists("/home/{$username}/.ssh/authorized_keys") && filesize("/home/{$username}/.ssh/authorized_keys") > 0;
        $jailed = strpos($shell, 'jk_lsh') !== false;
        return [
            'shell' => $shell,
            'jailed' => $jailed,
            'has_key' => $hasKey,
            'full_shell' => $shell === '/bin/bash',
            'no_access' => $shell === '/usr/sbin/nologin' || $shell === '/bin/false',
        ];
    }
}

function chown_r($path, $user) {
    exec("chown -R {$user}:{$user} {$path} 2>/dev/null");
}
