<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    
    protected static ?string $navigationLabel = 'Configuración';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Sistema';
    
    protected static ?int $navigationSort = 2;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = app(GeneralSettings::class);
        $this->form->fill([
            'agency_name' => $settings->agency_name ?? '',
            'agency_nit' => $settings->agency_nit ?? '',
            'agency_logo_path' => $settings->agency_logo_path ?? null,
            'drive_service_account_json' => $settings->drive_service_account_json ?? '',
            'smtp_host' => $settings->smtp_host ?? '',
            'smtp_port' => $settings->smtp_port ?? 587,
            'smtp_encryption' => $settings->smtp_encryption ?? 'tls',
            'smtp_username' => $settings->smtp_username ?? '',
            'smtp_password' => $settings->smtp_password ?? '',
            'smtp_from_address' => $settings->smtp_from_address ?? '',
            'smtp_from_name' => $settings->smtp_from_name ?? '',
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Datos Empresa')
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->schema([
                                Section::make('Marca Blanca / Identidad')
                                    ->schema([
                                        TextInput::make('agency_name')
                                            ->label('Nombre de la Agencia')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        TextInput::make('agency_nit')
                                            ->label('NIT / ID Fiscal')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        FileUpload::make('agency_logo_path')
                                            ->label('Logo Corporativo')
                                            ->image()
                                            ->directory('logos')
                                            ->visibility('public'),
                                    ])
                                    ->columns(2),
                            ]),
                        
                        Tab::make('Conexión Drive')
                            ->icon(Heroicon::OutlinedCloudArrowUp)
                            ->schema([
                                Section::make('API de Google Drive')
                                    ->description('Configura el Service Account JSON para conectar con Google Drive')
                                    ->schema([
                                        Textarea::make('drive_service_account_json')
                                            ->label('Google Service Account (JSON)')
                                            ->rows(10)
                                            ->helperText('Pega el contenido completo del archivo JSON del Service Account')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        
                        Tab::make('Correo & SMTP')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema([
                                Section::make('Servidor SMTP')
                                    ->description('Configura Zoho, Gmail o tu hosting de correo')
                                    ->schema([
                                        TextInput::make('smtp_host')
                                            ->label('Host SMTP')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        TextInput::make('smtp_port')
                                            ->label('Puerto')
                                            ->numeric()
                                            ->default(587)
                                            ->required(),
                                        
                                        Select::make('smtp_encryption')
                                            ->label('Cifrado')
                                            ->options([
                                                'ssl' => 'SSL',
                                                'tls' => 'TLS',
                                            ])
                                            ->default('tls')
                                            ->required(),
                                        
                                        TextInput::make('smtp_username')
                                            ->label('Usuario / Correo')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        
                                        TextInput::make('smtp_password')
                                            ->label('Contraseña')
                                            ->password()
                                            ->required(),
                                        
                                        TextInput::make('smtp_from_address')
                                            ->label('Dirección Remitente')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        
                                        TextInput::make('smtp_from_name')
                                            ->label('Nombre Remitente')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);
        
        $settings->agency_name = $data['agency_name'];
        $settings->agency_nit = $data['agency_nit'];
        $settings->agency_logo_path = $data['agency_logo_path'] ?? null;
        $settings->drive_service_account_json = $data['drive_service_account_json'] ?? null;
        $settings->smtp_host = $data['smtp_host'];
        $settings->smtp_port = $data['smtp_port'];
        $settings->smtp_encryption = $data['smtp_encryption'];
        $settings->smtp_username = $data['smtp_username'];
        $settings->smtp_password = $data['smtp_password'];
        $settings->smtp_from_address = $data['smtp_from_address'];
        $settings->smtp_from_name = $data['smtp_from_name'];
        
        $settings->save();
        
        \Filament\Notifications\Notification::make()
            ->title('Configuración guardada exitosamente')
            ->success()
            ->send();
    }
    
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar Cambios')
                ->action('save'),
        ];
    }
    
    public function getTitle(): string | Htmlable
    {
        return 'Configuración del Sistema';
    }
}
