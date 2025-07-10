<?php

namespace App\Filament\Resources\JadwalBookingResource\Pages;

use App\Filament\Resources\JadwalBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalBooking extends EditRecord
{
    protected static string $resource = JadwalBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
