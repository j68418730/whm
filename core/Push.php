<?php
/**
 * Push Service - Broadcasts real-time events to WebSocket clients
 * Integrates with the existing PHP app; called from controllers to push events
 */

namespace Core;

class Push
{
    private static $serverUrl = null;
    private static $secret = null;
    private static $enabled = true;

    public static function init(string $serverUrl, string $secret = '')
    {
        self::$serverUrl = rtrim($serverUrl, '/');
        self::$secret = $secret;
    }

    public static function setEnabled(bool $enabled): void
    {
        self::$enabled = $enabled;
    }

    /**
     * Broadcast an event to specific users or all connected agents
     */
    public static function broadcast(string $event, array $data, array $targetAdminIds = []): bool
    {
        if (!self::$enabled || !self::$serverUrl) {
            return false;
        }

        $payload = [
            'event' => $event,
            'data' => $data,
            'timestamp' => date('c'),
        ];

        if (!empty($targetAdminIds)) {
            $payload['targets'] = $targetAdminIds;
        }

        try {
            $ch = curl_init(self::$serverUrl . '/api/broadcast');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Push-Secret: ' . self::$secret,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 2,
                CURLOPT_CONNECTTIMEOUT => 1,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result !== false;
        } catch (\Throwable $e) {
            error_log("Push broadcast failed: " . $e->getMessage());
            return false;
        }
    }

    // Convenience methods for common support events

    public static function newChat(int $sessionId, array $chatData, array $targetAdminIds = []): bool
    {
        return self::broadcast('NEW_CHAT', array_merge(['session_id' => $sessionId], $chatData), $targetAdminIds);
    }

    public static function newMessage(int $sessionId, array $messageData, array $targetAdminIds = []): bool
    {
        return self::broadcast('NEW_MESSAGE', array_merge(['session_id' => $sessionId], $messageData), $targetAdminIds);
    }

    public static function chatTransferred(int $sessionId, int $fromAgentId, int $toAgentId, array $data = []): bool
    {
        return self::broadcast('CHAT_TRANSFERRED', array_merge(['session_id' => $sessionId, 'from_agent' => $fromAgentId, 'to_agent' => $toAgentId], $data), [$toAgentId]);
    }

    public static function customerTyping(int $sessionId, string $customerName): bool
    {
        return self::broadcast('CUSTOMER_TYPING', ['session_id' => $sessionId, 'customer_name' => $customerName]);
    }

    public static function agentTyping(int $sessionId, string $agentName): bool
    {
        return self::broadcast('AGENT_TYPING', ['session_id' => $sessionId, 'agent_name' => $agentName]);
    }

    public static function customerOffline(int $sessionId): bool
    {
        return self::broadcast('CUSTOMER_OFFLINE', ['session_id' => $sessionId]);
    }

    public static function ticketCreated(array $ticketData, array $targetAdminIds = []): bool
    {
        return self::broadcast('TICKET_CREATED', $ticketData, $targetAdminIds);
    }

    public static function ticketUpdated(array $ticketData, array $targetAdminIds = []): bool
    {
        return self::broadcast('TICKET_UPDATED', $ticketData, $targetAdminIds);
    }

    public static function newInternalMessage(array $messageData, array $targetAdminIds = []): bool
    {
        return self::broadcast('NEW_INTERNAL_MESSAGE', $messageData, $targetAdminIds);
    }

    public static function agentStatusChanged(int $adminId, string $status, string $message = ''): bool
    {
        return self::broadcast('AGENT_STATUS_CHANGED', ['admin_id' => $adminId, 'status' => $status, 'status_message' => $message]);
    }

    public static function notification(int $adminId, string $type, string $title, string $message, array $data = []): bool
    {
        return self::broadcast('NOTIFICATION', array_merge(['type' => $type, 'title' => $title, 'message' => $message], $data), [$adminId]);
    }
}