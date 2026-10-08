<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\Component;
use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;

/** Password reset with the same icons as the sign-in form. */
class ResetPassword extends BaseResetPassword
{
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->prefixIcon('heroicon-o-envelope');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()->prefixIcon('heroicon-o-lock-closed')->revealable();
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()->prefixIcon('heroicon-o-lock-closed')->revealable();
    }
}
