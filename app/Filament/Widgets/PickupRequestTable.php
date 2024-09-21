<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\PickupRequest;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;

class PickupRequestTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PickupRequest::query()
            )
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
            ]);
    }
}
