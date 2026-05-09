<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('action_label')
                    ->label('Aksi'),
                TextEntry::make('subject_label')
                    ->label('Entitas / Subject')
                    ->placeholder('-'),
                TextEntry::make('summary_label')
                    ->label('Ringkasan Aktivitas')
                    ->placeholder('-'),
                TextEntry::make('ip_address')
                    ->label('IP')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('properties')
                    ->label('Detail')
                    ->formatStateUsing(fn (?array $state): string => filled($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '-')
                    ->placeholder('-'),
            ]);
    }
}
