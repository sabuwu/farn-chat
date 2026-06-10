<?php
namespace app\Services;

use app\Repositories\UserRepository;
use Exception;
use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * @throws Exception
     */
    public function register(array $data): void
    {
        // 1. Validação do formato básico de E-mail
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("O formato do e-mail inserido é inválido... ;-;");
        }

        // 2. Validação do Username (Alfanumérico + underline, entre 3 e 32 caracteres)
        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $data['username'])) {
            throw new Exception("O usuário deve ter de 3 a 32 caracteres (letras, números e underlines)... ;-;");
        }

        // 3. A REGEX SUPREMA DA SENHA (Lookarounds)
        // - (?=.*[a-z]): Pelo menos uma letra minúscula
        // - (?=.*[A-Z]): Pelo menos uma letra maiúscula
        // - (?=.*\d): Pelo menos um número
        // - (?=.*[\W_]): Pelo menos um caractere especial (ou underline)
        // - .{8,}: No mínimo 8 caracteres de tamanho
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

        if (!preg_match($passwordRegex, $data['password'])) {
            throw new Exception("A senha deve ter no mínimo 8 caracteres, contendo pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial... ;-;");
        }

        // 4. Verificação de Duplicidade no MariaDB
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new Exception("Este e-mail já está cadastrado... ;-;");
        }

        if ($this->userRepository->findByUsername($data['username'])) {
            throw new Exception("Este nome de usuário já está em uso... ;-;");
        }

        // 5. Se passou por tudo, faz o Hash seguro e persiste
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $this->userRepository->create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $hashedPassword,
            'bio'           => $data['fullname'] ?? ''
        ]);
    }

    /**
     * @throws Exception
     */
    public function authenticate(string $email, string $password): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Por favor, insira um formato de e-mail válido... ;-;");
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception("E-mail ou senha incorretos... ;-;");
        }

        return [
            'id'   => $user['id'],
            'name' => $user['username']
        ];
    }

    /**
     * @throws Exception
     */
    public function generatePasswordReset(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Por favor, insira um formato de e-mail válido... ;-;");
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->userRepository->saveResetToken($user['id'], $token, $expiresAt);

            $resetLink = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost:8080'), '/') . '/reset-password?token=' . urlencode($token);
            $this->sendPasswordResetEmail($user['email'], $user['username'], $resetLink);
        }
    }

    /**
     * @throws Exception
     */
    public function resetPassword(string $token, string $password, string $passwordConfirmation): void
    {
        if ($token === '') {
            throw new Exception('Token de recuperação inválido... ;-;');
        }

        if ($password !== $passwordConfirmation) {
            throw new Exception('As senhas não conferem... ;-;');
        }

        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

        if (!preg_match($passwordRegex, $password)) {
            throw new Exception("A senha deve ter no mínimo 8 caracteres, contendo pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial... ;-;");
        }

        $user = $this->userRepository->findByResetToken($token);

        if (!$user) {
            throw new Exception('Token inválido ou expirado... ;-;');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepository->updatePasswordById((int) $user['id'], $hashedPassword);
    }

    /**
     * @throws Exception
     */
    private function sendPasswordResetEmail(string $recipientEmail, string $username, string $resetLink): void
    {
        try {
            $mailer = new PHPMailer(true);
            $mailer->CharSet = 'UTF-8';

            $host = trim((string) ($_ENV['MAIL_HOST'] ?? $_ENV['SMTP_HOST'] ?? ''));
            if ($host !== '') {
                $mailer->isSMTP();
                $mailer->Host = $host;
                $mailer->Port = (int) ($_ENV['MAIL_PORT'] ?? $_ENV['SMTP_PORT'] ?? 587);
                $mailer->SMTPAuth = true;
                $mailer->Username = (string) ($_ENV['MAIL_USERNAME'] ?? $_ENV['SMTP_USERNAME'] ?? '');
                $mailer->Password = (string) ($_ENV['MAIL_PASSWORD'] ?? $_ENV['SMTP_PASSWORD'] ?? '');

                $encryption = strtolower((string) ($_ENV['MAIL_ENCRYPTION'] ?? $_ENV['SMTP_ENCRYPTION'] ?? 'tls'));
                if ($encryption === 'ssl' || $encryption === 'tls') {
                    $mailer->SMTPSecure = $encryption;
                }
            } else {
                $mailer->isMail();
            }

            $fromAddress = (string) ($_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@localhost');
            $fromName = (string) ($_ENV['MAIL_FROM_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Farn-Chat'));

            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($recipientEmail, $username);
            $mailer->Subject = 'Recuperação de senha - Farn-Chat';
            $mailer->isHTML(true);
            $mailer->Body = sprintf(
                '<p>Olá, %s.</p><p>Recebemos um pedido para redefinir sua senha no Farn-Chat.</p><p><a href="%s">Clique aqui para criar uma nova senha</a></p><p>Este link expira em 1 hora.</p>',
                htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8')
            );
            $mailer->AltBody = "Olá, {$username}.\n\nAcesse o link para redefinir sua senha:\n{$resetLink}\n\nEste link expira em 1 hora.";

            $mailer->send();
        } catch (MailerException $e) {
            throw new Exception('Não foi possível enviar o e-mail de recuperação no momento.');
        }
    }
}
