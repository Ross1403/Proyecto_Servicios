<?php
// ============================================================
// includes/Webhook.php — Integración de Notificaciones a Slack/WhatsApp
// ============================================================

class Webhook {
    // Reemplaza con la URL real de tu Webhook de Slack, Discord, etc.
    // Ejemplo: 'https://hooks.slack.com/services/T000/B000/XXXX'
    private static $slackUrl = 'https://webhook.site/devioz-mock-url'; 

    /**
     * Envía una notificación a un Webhook (Ej: Slack o Teams)
     */
    public static function notifySalesTeam($subject, $details = []) {
        
        $message = "🚨 *Nuevo Lead / Notificación de Sistema*\n";
        $message .= "*Asunto:* " . $subject . "\n\n";
        
        foreach ($details as $key => $value) {
            $message .= "• *" . ucfirst($key) . ":* " . $value . "\n";
        }

        $payload = [
            'text' => $message,
            'username' => 'Devioz Bot',
            'icon_emoji' => ':robot_face:'
        ];

        // En un entorno de producción real, esto enviaría el cURL:
        /*
        $ch = curl_init(self::$slackUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_exec($ch);
        curl_close($ch);
        */

        // Como esto es un proyecto de práctica y no tenemos un Webhook real configurado,
        // guardamos la notificación simulada en un archivo de log para validarlo.
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [WEBHOOK TRIGGERED] " . json_encode($payload) . "\n";
        file_put_contents(__DIR__ . '/../webhook_mock.log', $logMessage, FILE_APPEND);
        
        return true; // Simula éxito
    }
}
