<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;

class CreateAdmin extends Command
{
    protected $signature = 'mpk:admin {email : Email untuk login} {--name=Admin MPK : Nama admin} {--password= : Password (min. 8 karakter); kosongkan untuk ditanya}';

    protected $description = 'Buat akun admin baru, atau ganti password bila email sudah ada';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }
        $pass = (string) ($this->option('password') ?: password('Password admin (min. 8 karakter)'));
        if (strlen($pass) < 8) {
            $this->error('Password admin minimal 8 karakter.');

            return self::FAILURE;
        }

        $user = User::firstWhere('email', $email);
        if ($user) {
            $user->update(['password' => $pass]);
            $this->info('Password admin diperbarui.');
        } else {
            User::create(['name' => $this->option('name'), 'email' => $email, 'password' => $pass, 'role' => 'admin']);
            $this->info("Admin dibuat: $email");
        }

        return self::SUCCESS;
    }
}
