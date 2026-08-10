<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SignaturePad extends Field
{
    protected string $view = 'filament.forms.components.signature-pad';

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('regex:/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/');
        $this->validationMessages([
            'regex' => 'Format tanda tangan tidak valid. Silakan hapus lalu tanda tangani kembali.',
        ]);
    }
}
