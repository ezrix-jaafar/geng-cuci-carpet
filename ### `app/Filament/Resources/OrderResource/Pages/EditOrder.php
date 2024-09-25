<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use App\Events;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->color('info') // Change the button color to blue
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    // Check if the order has any items
                    if ($this->record->orderItems->isEmpty()) {
                        Notification::make()
                            ->title('Error: No Items Found')
                            ->body('Please add items to the order before generating an invoice.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Calculate the total price
                    $totalPrice = $this->record->orderItems->sum('price');

                    // Update the total price in the order
                    $this->record->update(['total_price' => $totalPrice]);

                    // Generate the invoice
                    $invoice = $this->record->generateInvoice();

                    // Update the order status to "Waiting For Payment"
                    $this->record->update(['status' => 'Waiting For Payment']);

                    Notification::make()
                        ->title('Invoice Generated')
                        ->success()
                        ->send();

                    return new RedirectResponse(url()->current());
                })
                ->visible(fn () => !$this->record->invoice)
                ->requiresConfirmation(),
        ];
    }
}
