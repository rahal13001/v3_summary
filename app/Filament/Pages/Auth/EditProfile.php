<?php
 
namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
 
class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
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
