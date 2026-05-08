<?php

namespace App\Filament\Resources\Pengampus;

use App\Filament\RoleGate;

use App\Filament\Resources\Pengampus\Pages\CreatePengampu;
use App\Filament\Resources\Pengampus\Pages\EditPengampu;
use App\Filament\Resources\Pengampus\Pages\ListPengampus;
use App\Filament\Resources\Pengampus\Pages\ViewPengampu;
use App\Filament\Resources\Pengampus\Schemas\PengampuForm;
use App\Filament\Resources\Pengampus\Schemas\PengampuInfolist;
use App\Filament\Resources\Pengampus\Tables\PengampusTable;
use App\Models\Pengampu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengampuResource extends Resource
{
    protected static ?string $model = Pengampu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


    public static function canViewAny(): bool
    {
        return RoleGate::admin();
    }

    public static function canCreate(): bool
    {
        return RoleGate::admin();
    }

    public static function canEdit($record): bool
    {
        return RoleGate::admin();
    }

    public static function canDelete($record): bool
    {
        return RoleGate::admin();
    }
    public static function form(Schema $schema): Schema
    {
        return PengampuForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PengampuInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengampusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengampus::route('/'),
            'create' => CreatePengampu::route('/create'),
            'view' => ViewPengampu::route('/{record}'),
            'edit' => EditPengampu::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}


