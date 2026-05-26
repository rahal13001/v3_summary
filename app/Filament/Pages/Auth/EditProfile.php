<?php
 
namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
 
class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->image()
                    ->nullable()
                    ->disk('public')
                    ->directory('foto-pegawai')
                    ->visibility('public')
                    ->avatar()
                    ->maxSize(1500)
                    ->maxFiles(1),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('nip')
                    ->label('NIP')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->nullable()
                    ->maxLength(255),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}