<?php

class Mailer
{
    protected array $to = [];
    protected string $subject = '';
    protected string $body = '';
    protected string $headers = '';
    protected bool $isHtml = true;

    public function __construct()
    {
        $fromEmail = Config::get('smtp.from_email', 'noreply@localhost');
        $fromName = Config::get('smtp.from_name', 'Leccionario Digital');
        
        $this->headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $this->headers .= "MIME-Version: 1.0\r\n";
        $this->headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }

    public function to(string $email, string $name = ''): self
    {
        $this->to[] = $name ? "{$name} <{$email}>" : $email;
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body, bool $isHtml = true): self
    {
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    public function send(): bool
    {
        if (empty($this->to) || empty($this->subject) || empty($this->body)) {
            return false;
        }

        if (!$this->isHtml) {
            $this->headers = str_replace('Content-Type: text/html', 'Content-Type: text/plain', $this->headers);
        }

        $to = implode(', ', $this->to);
        
        return mail($to, $this->subject, $this->body, $this->headers);
    }

    public static function sendReminder(int $userId, string $message): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM usuarios WHERE id = :id", ['id' => $userId]);
        
        if (!$user) {
            return false;
        }

        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Leccionario Digital</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola, {$user->nombre}!</h2>
                    <p>{$message}</p>
                    <p>
                        <a href='" . Config::basePath() . "' class='btn'>Acceder al sistema</a>
                    </p>
                </div>
            </div>
        </body>
        </html>";

        $mailer = new self();
        $mailer->to($user->email, $user->nombre . ' ' . $user->apellido)
               ->subject('Recordatorio - Leccionario Digital')
               ->body($body);
        
        $result = $mailer->send();

        $db->insert('logs_notificaciones', [
            'usuario_id' => $userId,
            'tipo' => 'recordatorio',
            'asunto' => 'Recordatorio - Leccionario Digital',
            'mensaje' => $message,
            'enviado' => $result ? 1 : 0,
            'fecha_envio' => $result ? date('Y-m-d H:i:s') : null
        ]);

        return $result;
    }

    public static function sendWelcome(int $userId): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM usuarios WHERE id = :id", ['id' => $userId]);
        
        if (!$user) {
            return false;
        }

        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .btn { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenido/a</h1>
                </div>
                <div class='content'>
                    <h2>¡Hola, {$user->nombre}!</h2>
                    <p>Tu cuenta en <strong>Leccionario Digital</strong> ha sido creada exitosamente.</p>
                    <p>Ya puedes comenzar a registrar tus leccionarios de manera digital.</p>
                    <p>
                        <a href='" . Config::basePath() . "' class='btn'>Acceder al sistema</a>
                    </p>
                </div>
            </div>
        </body>
        </html>";

        $mailer = new self();
        $mailer->to($user->email, $user->nombre . ' ' . $user->apellido)
               ->subject('Bienvenido/a - Leccionario Digital')
               ->body($body);
        
        $result = $mailer->send();

        $db->insert('logs_notificaciones', [
            'usuario_id' => $userId,
            'tipo' => 'bienvenida',
            'asunto' => 'Bienvenido/a - Leccionario Digital',
            'mensaje' => 'Cuenta creada exitosamente',
            'enviado' => $result ? 1 : 0,
            'fecha_envio' => $result ? date('Y-m-d H:i:s') : null
        ]);

        return $result;
    }

    public function clear(): void
    {
        $this->to = [];
        $this->subject = '';
        $this->body = '';
    }
}
