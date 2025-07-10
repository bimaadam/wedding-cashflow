<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemasukanKasResource\Pages;
use App\Models\PemasukanKas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PemasukanKasResource extends Resource
{
    protected static ?string $model = PemasukanKas::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Pemasukan Kas';
    protected static ?string $pluralModelLabel = 'Pemasukan Kas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                Forms\Components\Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'nama_event')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('pemasukan')
                    ->label('Pemasukan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('total_pemasukan')
                    ->label('Total Pemasukan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date(),

                Tables\Columns\TextColumn::make('event.nama_event')
                    ->label('Event'),

                Tables\Columns\TextColumn::make('pemasukan')
                    ->label('Pemasukan')
                    ->money('IDR', true),

                Tables\Columns\TextColumn::make('total_pemasukan')
                    ->label('Total Pemasukan')
                    ->money('IDR', true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPemasukanKas::route('/'),
            'create' => Pages\CreatePemasukanKas::route('/create'),
            'edit' => Pages\EditPemasukanKas::route('/{record}/edit'),
        ];
    }
}
