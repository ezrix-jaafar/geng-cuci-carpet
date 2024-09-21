<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use App\Models\Invoice;
use App\Services\ToyyibPayService;


class InvoiceRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('invoice_number')
                ->disabled()
                ->default(function () {
                    $latestInvoice = Invoice::latest('id')->first();
                    $nextId = $latestInvoice ? $latestInvoice->id + 1 : 1;
                    return 'INV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                })
                ->required(),
                TextInput::make('client_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('client_address')
                    ->required()
                    ->maxLength(255),
                TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number'),
                Tables\Columns\TextColumn::make('client_name'),
                Tables\Columns\TextColumn::make('client_address'),
                Tables\Columns\TextColumn::make('total_price'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            // Delete the invoice
                            $record->delete();

                            // Display success notification
                            Notification::make()
                                ->title('Invoice Deleted')
                                ->success()
                                ->send();

                            // Redirect back to the current page
                            return redirect()->back();
                        }),
                    Action::make('generatePDF')
                        ->label('Generate PDF')
                        ->icon('heroicon-o-printer')
                        ->action(function ($record) {
                            return $this->generatePDF($record);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    protected function generatePDF($invoice)
    {

        // Generate the ToyyibPay payment link
        $toyyibPayService = new ToyyibPayService();
        $paymentLink = $toyyibPayService->createPaymentLink($invoice->order);

        // Ensure the payment link is not null
        if ($paymentLink === null) {
            Notification::make()
                ->title('Error Generating Payment Link')
                ->body('Unable to generate payment link. Please try again later.')
                ->danger()
                ->send();
            return;
        }


        $data = [
            'client_name' => $invoice->client_name,
            'client_address' => $invoice->client_address,
            'order_number' => $invoice->order_number,
            'total_price' => $invoice->total_price,
            'order_items' => $invoice->order->orderItems,
            'payment_link' => $paymentLink, // Include the payment link in the data
        ];

        $pdf = Pdf::loadView('pdfs.invoice', $data);
        $pdf->setPaper('A4');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'invoice_' . $invoice->order_number . '.pdf');
    }
}
