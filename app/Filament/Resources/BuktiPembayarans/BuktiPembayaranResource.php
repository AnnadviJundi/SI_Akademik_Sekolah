<?php

namespace App\Filament\Resources\BuktiPembayarans;

use App\Filament\RoleGate;
use App\Filament\Resources\BuktiPembayarans\Pages\CreateBuktiPembayaran;
use App\Filament\Resources\BuktiPembayarans\Pages\EditBuktiPembayaran;
use App\Filament\Resources\BuktiPembayarans\Pages\ListBuktiPembayarans;
use App\Filament\Resources\BuktiPembayarans\Pages\ViewBuktiPembayaran;
use App\Filament\Resources\BuktiPembayarans\Schemas\BuktiPembayaranForm;
use App\Filament\Resources\BuktiPembayarans\Schemas\BuktiPembayaranInfolist;
use App\Filament\Resources\BuktiPembayarans\Tables\BuktiPembayaransTable;
use App\Models\BuktiPembayaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuktiPembayaranResource extends Resource
{
    protected static ?string $model = BuktiPembayaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Bukti Pembayaran';

    public static function canViewAny(): bool
    {
        return RoleGate::has('admin', 'staf_tu', 'siswa');
    }

    public static function canCreate(): bool
    {
        return RoleGate::has('admin', 'staf_tu');
    }

    public static function canEdit($record): bool
    {
        return RoleGate::has('admin', 'staf_tu');
    }

    public static function canDelete($record): bool
    {
        return RoleGate::has('admin', 'staf_tu');
    }

    public static function form(Schema $schema): Schema
    {
        return BuktiPembayaranForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BuktiPembayaranInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuktiPembayaransTable::configure($table);
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
            'index' => ListBuktiPembayarans::route('/'),
            'create' => CreateBuktiPembayaran::route('/create'),
            'view' => ViewBuktiPembayaran::route('/{record}'),
            'edit' => EditBuktiPembayaran::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = RoleGate::user();

        if (RoleGate::has('admin', 'staf_tu')) {
            return $query;
        }

        if (RoleGate::has('siswa')) {
            return $query->whereHas('pembayaran', fn (Builder $query) => $query->where('siswa_id', $user?->siswa?->id));
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
