<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\DateColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Livewire;
use App\Livewire\UpdateOrderTotal;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('order_number')
                                    ->disabled()
                                    ->default(function () {
                                        $latestOrder = Order::latest('id')->first();
                                        $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
                                        return 'ORD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                                    })
                                    ->required(),
                                Select::make('pickup_request_id')
                                    ->relationship('pickupRequest', 'request_number')
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'New Order' => 'New Order',
                                        'Waiting For Payment' => 'Waiting For Payment',
                                        'Invoice Paid' => 'Invoice Paid',
                                        'Cleaning' => 'Cleaning',
                                        'Cleaning Complete' => 'Cleaning Complete',
                                        'Waiting For Delivery' => 'Waiting For Delivery',
                                        'Delivered' => 'Delivered',
                                    ])->default('New Order'),
                                Select::make('client_id')
                                    ->relationship('client', 'name')
                                    ->required(),
                                Select::make('agent_id')
                                    ->relationship('agent', 'name')
                                    ->required(),
                                DatePicker::make('delivery_date'),
                            ]),
                    ]),
                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('total_price')
                            ->disabled()
                            ->placeholder('-'),
                        Textarea::make('notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->sortable()->searchable(),
                TextColumn::make('client.name')->sortable()->searchable(),
                TextColumn::make('agent.name')->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'New Order' => 'primary',
                        'Waiting For Payment' => 'warning',
                        'Invoice Paid' => 'info',
                        'Cleaning' => 'info',
                        'Cleaning Complete' => 'success',
                        'Waiting For Delivery' => 'secondary',
                        'Delivered' => 'success',
                    ]),
                TextColumn::make('total_price')->sortable()
                    ->placeholder('-'),
                TextColumn::make('delivery_date')->sortable(),
                TextColumn::make('created_at')->label('Created')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('generateInvoice')
                        ->label('Generate Invoice')
                        ->icon('heroicon-o-document-text')
                        ->action(function ($record) {
                            // Check if the order has items
                            if ($record->orderItems->isEmpty()) {
                                Notification::make()
                                    ->title('Cannot Generate Invoice')
                                    ->body('The order has no items.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Generate the invoice
                            $invoice = $record->generateInvoice();
                            // Update the order status to "Waiting For Payment"
                            $record->update(['status' => 'Waiting For Payment']);
                            Notification::make()
                                ->title('Invoice Generated')
                                ->success()
                                ->send();
                            return new RedirectResponse(url()->current());
                        })
                        ->visible(fn ($record) => !$record->invoice)
                        ->requiresConfirmation(),
                    Action::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Select::make('status')
                                ->options([
                                    'New Order' => 'New Order',
                                    'Waiting For Payment' => 'Waiting For Payment',
                                    'Invoice Paid' => 'Invoice Paid',
                                    'Cleaning' => 'Cleaning',
                                    'Cleaning Complete' => 'Cleaning Complete',
                                    'Waiting For Delivery' => 'Waiting For Delivery',
                                    'Delivered' => 'Delivered',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $record) {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Status Updated')
                                ->success()
                                ->send();
                        }),
                ])
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
            RelationManagers\OrderItemsRelationManager::class,
            RelationManagers\InvoiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            // Default navigation for listing Pickup Requests
            NavigationItem::make('Orders')
                ->url(static::getUrl()) // Default URL for listing Pickup Requests
                ->group('Order'), // Group under "Pickup"
            
            // Custom navigation for "Create Pickup Request"
            NavigationItem::make('Create Order')
                ->url(static::getUrl('create')) // URL for the create form
                ->group('Order'), // Group under "Pickup"
        ];
    }
}
