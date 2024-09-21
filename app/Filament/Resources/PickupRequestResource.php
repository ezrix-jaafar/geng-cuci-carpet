<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickupRequestResource\Pages;
use App\Filament\Resources\PickupRequestResource\RelationManagers;
use App\Models\PickupRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\DateColumn;
use Filament\Tables\Actions\Action;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Agent;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use App\Models\Client;
use Filament\Navigation\NavigationItem;
class PickupRequestResource extends Resource
{
    protected static ?string $model = PickupRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Section::make('Request Information')
                            ->schema([
                                TextInput::make('request_number')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(function () {
                                        $latestRequest = PickupRequest::latest('id')->first();
                                        $nextId = $latestRequest ? $latestRequest->id + 1 : 1;
                                        return 'PR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                                    })
                                    ->required(),
                                Select::make('client_id')
                                    ->relationship('client', 'name')
                                    ->required(),
                                TextInput::make('estimated_item_qty')->required(),
                            ])->columnSpan(1),
                        Section::make('Pickup Details')
                            ->schema([
                                Select::make('agent_id')
                                    ->label('Agent')
                                    ->options(Agent::query()->pluck('name', 'id'))
                                    ->searchable(),
                                Select::make('status')
                                    ->options([
                                        'Pending' => 'Pending',
                                        'Assigned' => 'Assigned',
                                        'Pickup Complete' => 'Pickup Complete',
                                    ])->default('Pending'),
                                DatePicker::make('pickup_date'),
                            ])->columnSpan(1),
                    ]),
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes'),
                    ])
                    ->columnSpan(2),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')->sortable()->searchable()
                ->action(
                    ViewAction::make()
                        ->modalHeading('Pickup Request Details')
                        ->form(fn (PickupRequest $record): array => [
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('request_number')
                                        ->disabled()
                                        ->columnSpan(2),
                                    Select::make('client_id')
                                        ->label('Client Name')
                                        ->options(Client::query()->pluck('name', 'id'))
                                        ->disabled()
                                        ->columnSpan(1),
                                    Select::make('agent_id')
                                        ->label('Agent Name')
                                        ->options(Agent::query()->pluck('name', 'id'))
                                        ->disabled()
                                        ->columnSpan(1),
                                    TextInput::make('estimated_item_qty')
                                        ->label('Estimated Item Qty')
                                        ->disabled()
                                        ->columnSpan(1),
                                    TextInput::make('final_item_qty')
                                        ->disabled()
                                        ->columnSpan(1),
                                    TextInput::make('status')
                                        ->disabled()
                                        ->columnSpan(1),
                                    DatePicker::make('pickup_date')
                                        ->disabled()
                                        ->columnSpan(1),
                                    Forms\Components\Textarea::make('notes')
                                        ->disabled()
                                        ->columnSpan(2),
                                ]),
                        ])
                        ->modalActions([
                            EditAction::make()
                                    ->modalSubmitActionLabel('Save')
                                    ->form(fn (PickupRequest $record): array => [
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('request_number')->disabled()->columnSpan(2),
                                                Select::make('client_id')
                                                    ->label('Client Name')
                                                    ->options(Client::query()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->columnSpan(1),
                                                Select::make('agent_id')
                                                    ->label('Agent')
                                                    ->options(Agent::query()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->columnSpan(1),
                                                TextInput::make('estimated_item_qty')->columnSpan(1),
                                                TextInput::make('final_item_qty')->columnSpan(1),
                                                TextInput::make('status')->columnSpan(1),
                                                DatePicker::make('pickup_date')->columnSpan(1),
                                                Forms\Components\Textarea::make('notes')->columnSpan(2),
                                            ]),
                                    ]),
                            Action::make('convertToOrder')
                                ->label('Convert to Order')
                                ->icon('heroicon-o-arrow-right-circle')
                                ->form([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('final_item_qty')
                                                ->label('Final Item Quantity')
                                                ->required()
                                                ->numeric()
                                                ->minValue(1)
                                                ->columnSpan(1),
                                            DatePicker::make('pickup_date')
                                                ->label('Pickup Date')
                                                ->required()
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->action(function (PickupRequest $record, array $data) {
                                    // Update the PickupRequest with final details
                                    $record->update([
                                        'final_item_qty' => $data['final_item_qty'],
                                        'pickup_date' => $data['pickup_date'],
                                        'status' => 'Pickup Complete'
                                    ]);

                                    // Create the Order
                                    $order = Order::create([
                                        'client_id' => $record->client_id,
                                        'agent_id' => $record->agent_id,
                                        'item_qty' => $data['final_item_qty'],
                                        'status' => 'Pending',
                                        'pickup_date' => $data['pickup_date'],
                                        'notes' => $record->notes,
                                        'pickup_request_id' => $record->id,
                                        'order_number' => 'ORD-' . str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT),
                                    ]);

                                    Notification::make()
                                        ->title('Pickup Request converted to Order successfully')
                                        ->success()
                                        ->send();

                                    return redirect()->route('filament.admin.resources.orders.edit', $order);
                                })
                                ->requiresConfirmation()
                                ->color('success')
                                ->hidden(fn (PickupRequest $record) => $record->status === 'Pickup Complete')
                        ]),
                    ),
                TextColumn::make('client.name')->sortable()->searchable(),
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->getStateUsing(function ($record) {
                        return $record->agent ? $record->agent->name : 'No Agent';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('agent', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        $query->orderBy(Agent::select('name')
                            ->whereColumn('agents.id', 'pickup_requests.agent_id'),
                            $direction
                        );
                    }),
                TextColumn::make('estimated_item_qty')->label('Estimated Qty')->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'Pending',
                        'info' => 'Assigned',
                        'success' => 'Pickup Complete',
                    ])
                    ->sortable(),
                TextColumn::make('pickup_date')->date()->sortable(),
                TextColumn::make('created_at')->label('Created')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    //Assigne agent button
                    Action::make('assignAgent')
                    ->label('Assign Agent')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('agent_id')
                            ->label('Select Agent')
                            ->options(Agent::all()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (PickupRequest $record, array $data): void {
                        $record->update([
                            'agent_id' => $data['agent_id'],
                            'status' => 'Assigned',
                        ]);

                        Notification::make()
                            ->title('Agent Assigned Successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PickupRequest $record): bool => $record->status === 'Pending'),
                    //Convert to order button
                    Action::make('convert_to_order')
                        ->label('Convert to Order')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->form([
                            DatePicker::make('pickup_date')
                                ->label('Pickup Date')
                                ->required(),
                        ])
                        ->action(function (PickupRequest $record, array $data) {
                            // Update the PickupRequest with final details
                            $record->update([
                                'pickup_date' => $data['pickup_date'],
                                'status' => 'Pickup Complete'
                            ]);

                            // Create the Order
                            $order = Order::create([
                                'client_id' => $record->client_id,
                                'agent_id' => $record->agent_id,
                                'status' => 'New Order',
                                'pickup_date' => $data['pickup_date'],
                                'notes' => $record->notes,
                                'pickup_request_id' => $record->id,
                                'order_number' => 'ORD-' . str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT),
                            ]);

                            Notification::make()
                                ->title('Pickup Request converted to Order successfully')
                                ->success()
                                ->send();

                            return redirect()->route('filament.admin.resources.orders.edit', $order);
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->hidden(fn (PickupRequest $record) => $record->status === 'Pickup Complete' || is_null($record->agent_id))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPickupRequests::route('/'),
            'create' => Pages\CreatePickupRequest::route('/create'),
            // 'edit' => Pages\EditPickupRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            // Default navigation for listing Pickup Requests
            NavigationItem::make('Pickup Requests')
                ->url(static::getUrl()) // Default URL for listing Pickup Requests
                ->group('Pickup'), // Group under "Pickup"
            
            // Custom navigation for "Create Pickup Request"
            NavigationItem::make('Create Pickup Request')
                ->url(static::getUrl('create')) // URL for the create form
                ->group('Pickup'), // Group under "Pickup"
        ];
    }
}
