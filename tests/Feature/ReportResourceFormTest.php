<?php

namespace Tests\Feature;

use App\Filament\Forms\Components\SignaturePad;
use App\Filament\Resources\ReportResource;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Component;
use ReflectionClass;
use Tests\TestCase;

class ReportResourceFormTest extends TestCase
{
    public function test_report_resource_uses_the_local_signature_pad(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString('App\\Filament\\Forms\\Components\\SignaturePad', $source);
        $this->assertStringNotContainsString('Saade\\FilamentAutograph', $source);
        $this->assertStringContainsString("SignaturePad::make('kode')", $source);
        $this->assertStringContainsString("->label('Tanda Tangan Penyusun')", $source);
        $this->assertStringContainsString("->helperText('Pastikan tanda tangan terlihat jelas sebelum menyimpan laporan.')", $source);
        $this->assertStringContainsString("->required(fn (string \$operation): bool => \$operation === 'create')", $source);
        $this->assertTrue(is_subclass_of(SignaturePad::class, Component::class));
    }

    public function test_report_rich_editor_disables_file_attachments(): void
    {
        $editor = RichEditor::make('how')->fileAttachments(false);
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertFalse($editor->hasFileAttachments(default: true));
        $this->assertStringContainsString('->fileAttachments(false)', $source);
        $this->assertStringContainsString('->toolbarButtons([', $source);
        $this->assertStringNotContainsString("'attachFiles'", $source);
    }

    public function test_report_rich_editor_exposes_the_table_toolbar(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString("['table']", $source);
    }

    public function test_team_filter_options_are_keyed_by_the_team_id(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString("Team::pluck('nama_tim', 'id')", $source);
        $this->assertStringNotContainsString("Team::pluck('nama_tim')", $source);
    }

    public function test_report_uploads_reject_wildcard_images_and_svg_active_content(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringNotContainsString("'image/*'", $source);
        $this->assertStringNotContainsString("'image/svg+xml'", $source);
        $this->assertStringContainsString("'image/jpeg', 'image/png', 'image/webp'", $source);
    }

    public function test_report_form_is_split_into_workflow_sections(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        foreach ([
            'Penyusun Laporan',
            'Ringkasan Kegiatan',
            'Pelaksanaan dan Peserta',
            'Dokumentasi',
            'Pengesahan',
        ] as $section) {
            $this->assertStringContainsString("Section::make('{$section}')", $source);
        }
    }

    public function test_report_form_collects_required_organization_dimensions(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString("Select::make('workUnits')", $source);
        $this->assertStringContainsString("->relationship('workUnits', 'name'", $source);
        $this->assertMatchesRegularExpression("/Select::make\('workUnits'\).*?->multiple\(\).*?->minItems\(1\).*?->required\(\)/s", $source);
        $this->assertStringContainsString("Select::make('involvement_id')", $source);
        $this->assertStringContainsString("->relationship('involvement', 'name'", $source);
        $this->assertMatchesRegularExpression("/Select::make\('involvement_id'\).*?->live\(\).*?->afterStateUpdated\(/s", $source);
        $this->assertStringContainsString('->afterStateHydrated(', $source);
        $this->assertStringContainsString("\$set('penyelenggara', \$involvement?->organizerName())", $source);
        $this->assertMatchesRegularExpression("/TextInput::make\('penyelenggara'\).*?->readOnly\(.*?is_lprl_organizer.*?\).*?->required\(\)/s", $source);
    }

    public function test_report_detail_and_filters_expose_organization_dimensions(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString("TextEntry::make('workUnits.name')", $source);
        $this->assertStringContainsString("TextEntry::make('involvement.name')", $source);
        $this->assertStringContainsString("SelectFilter::make('workUnits')", $source);
        $this->assertStringContainsString("->relationship('workUnits', 'name')", $source);
        $this->assertStringContainsString("SelectFilter::make('involvement')", $source);
        $this->assertStringContainsString("->relationship('involvement', 'name')", $source);
    }

    public function test_report_sections_use_the_full_form_width_while_compact_fields_remain_grouped(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertMatchesRegularExpression('/->components\(\[.*?\]\)\s*->columns\(1\);/s', $source);
        $this->assertStringContainsString("Fieldset::make('Berkas dokumentasi')", $source);
        $this->assertStringContainsString("'xl' => 3", $source);
        $this->assertMatchesRegularExpression('/Fieldset::make\(\'Berkas dokumentasi\'\).*?->columnSpanFull\(\)\s*->schema\(\[/s', $source);
    }

    public function test_signature_pad_is_protected_from_livewire_dom_updates_and_has_a_sized_canvas(): void
    {
        $view = file_get_contents(resource_path('views/filament/forms/components/signature-pad.blade.php'));

        $this->assertStringContainsString('wire:ignore', $view);
        $this->assertStringContainsString('signature-pad__canvas', $view);
        $this->assertStringContainsString('min-height: 15rem', $view);
        $this->assertStringContainsString('releasePointerCapture', $view);
        $this->assertStringContainsString('canvasMetrics()', $view);
        $this->assertStringNotContainsString('prepareCanvas()', $view);
        $this->assertStringNotContainsString('.\\$refs', $view);
    }

    public function test_signature_uses_a_theme_only_inversion_while_the_saved_png_stays_black(): void
    {
        $signaturePadView = file_get_contents(resource_path('views/filament/forms/components/signature-pad.blade.php'));
        $themeView = file_get_contents(resource_path('views/filament/styles/signature-theme.blade.php'));
        $resource = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringNotContainsString("strokeStyle = '#ffffff'", $signaturePadView);
        $this->assertStringContainsString(':root.dark .signature-pad__canvas', $themeView);
        $this->assertStringContainsString(':root.dark .signature-preview__image', $themeView);
        $this->assertStringContainsString('filter: invert(1)', $themeView);
        $this->assertStringContainsString("->extraImgAttributes(['class' => 'signature-preview__image report-infolist-signature'])", $resource);
    }

    public function test_dark_signature_preview_normalizes_any_saved_ink_to_white(): void
    {
        $themeView = file_get_contents(resource_path('views/filament/styles/signature-theme.blade.php'));

        $this->assertStringContainsString('.dark .signature-preview__image', $themeView);
        $this->assertStringContainsString('filter: brightness(0) invert(1)', $themeView);
        $this->assertStringContainsString('.report-infolist-section--signature .fi-in-image', $themeView);
        $this->assertStringContainsString('.dark .report-infolist-section--signature .fi-in-image', $themeView);
    }

    public function test_report_resource_tolerates_imported_records_without_local_documentation_files(): void
    {
        $source = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString('$schema->getRecord()', $source);
        $this->assertStringNotContainsString('$schema->record', $source);
        $this->assertStringContainsString("Select::make('followers')", $source);
        $this->assertStringContainsString("Select::make('indicators')", $source);
        $this->assertStringContainsString("->relationship('teams', 'nama_tim')", $source);
        $this->assertStringContainsString("Storage::disk('public')->exists", $source);
        $this->assertStringContainsString('$dokumentasi?->dokumentasi1', $source);
    }

    public function test_view_report_infolist_uses_one_full_width_column(): void
    {
        $source = file_get_contents(base_path('app/Filament/Resources/ReportResource/Pages/ViewReport.php'));
        $resource = file_get_contents((new ReflectionClass(ReportResource::class))->getFileName());

        $this->assertStringContainsString('public function defaultInfolist(Schema $schema): Schema', $source);
        $this->assertStringContainsString('parent::defaultInfolist($schema)->columns(1)', $source);
        $this->assertStringContainsString("->extraAttributes(['class' => 'report-infolist-section", $resource);
        $this->assertStringContainsString("TextEntry::make('why')", $resource);
        $this->assertStringContainsString("->extraAttributes(['class' => 'report-infolist-rich-text'])", $resource);
    }

    public function test_report_infolist_has_responsive_theme_hooks_for_long_content(): void
    {
        $theme = file_get_contents(resource_path('views/filament/styles/signature-theme.blade.php'));
        $how = file_get_contents(resource_path('views/infolists/components/how.blade.php'));

        $this->assertStringContainsString('.report-infolist-section', $theme);
        $this->assertStringContainsString('.report-infolist-rich-text', $theme);
        $this->assertStringContainsString('.dark .report-infolist-section', $theme);
        $this->assertStringContainsString('overflow-wrap: anywhere', $how);
        $this->assertStringContainsString('min-width: 36rem', $how);
    }
}
