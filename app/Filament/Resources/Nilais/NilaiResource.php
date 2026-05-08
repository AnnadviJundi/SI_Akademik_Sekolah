<?php

namespace App\Filament\Resources\Nilais;

use App\Filament\RoleGate;
use App\Filament\Resources\Nilais\Pages\CreateNilai;
use App\Filament\Resources\Nilais\Pages\EditNilai;
use App\Filament\Resources\Nilais\Pages\ListNilais;
use App\Filament\Resources\Nilais\Pages\ViewNilai;
use App\Filament\Resources\Nilais\Schemas\NilaiForm;
use App\Filament\Resources\Nilais\Schemas\NilaiInfolist;
use App\Filament\Resources\Nilais\Tables\NilaisTable;
use App\Models\Nilai;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Nilai';

    public static function canViewAny(): bool
    {
        return RoleGate::has('admin', 'guru', 'siswa');
    }

    public static function canCreate(): bool
    {
        return RoleGate::has('admin', 'guru');
    }

    public static function canEdit($record): bool
    {
        return RoleGate::admin() || (RoleGate::has('guru') && $record->guru_id === RoleGate::user()?->guru?->id);
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Schema $schema): Schema
    {
        return NilaiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NilaiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NilaisTable::configure($table);
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
            'index' => ListNilais::route('/'),
            'create' => CreateNilai::route('/create'),
            'view' => ViewNilai::route('/{record}'),
            'edit' => EditNilai::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = RoleGate::user();

        if (RoleGate::admin()) {
            return $query;
        }

        if (RoleGate::has('guru')) {
            return $query->where('guru_id', $user?->guru?->id);
        }

        if (RoleGate::has('siswa')) {
            return $query->where('siswa_id', $user?->siswa?->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
