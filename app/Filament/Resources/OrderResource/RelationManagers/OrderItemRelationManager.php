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



class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $recordTitleAttribute = 'item_description';

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('item_description')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('carpet_size')
                ->numeric(),
            Forms\Components\TextInput::make('carpet_type')
                ->required()
                ->maxLength(255),
            TextInput::make('price')
                ->numeric(),
        ]);
}

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_description'),
                Tables\Columns\TextColumn::make('carpet_size'),
                Tables\Columns\TextColumn::make('carpet_type'),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('label_number'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('generateLabel')
                        ->label('Generate Label')
                        ->icon('heroicon-o-printer')
                        ->action(function ($record) {
                            return $this->generateLabel($record);
                        })
                        ])
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('generateBulkLabels')
                        ->label('Generate Labels')
                        ->icon('heroicon-o-printer')
                        ->action(function (Collection $records) {
                            return $this->generateBulkLabels($records);
                        })
                ]),
            ]);
    }

    protected function generateLabel($orderItem)
    {
        $order = $orderItem->order;
        $totalItems = $order->orderItems->count();
        $currentItemIndex = $order->orderItems->search(function ($item) use ($orderItem) {
            return $item->id === $orderItem->id;
        }) + 1;

        $labelNumber = $currentItemIndex . '/' . $totalItems;
        $filenameLabelNumber = $currentItemIndex . '_of_' . $totalItems;

        $data = [
            'order_number' => $order->order_number,
            'agent_name' => $order->agent->name,
            'order_date' => $order->created_at->format('Y-m-d'),
            'label_number' => $labelNumber,
        ];

        $pdf = Pdf::loadView('pdfs.item-label', $data);
        $pdf->setPaper('A6');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'label_' . $order->order_number . '_' . $filenameLabelNumber . '.pdf');
    }

    protected function generateBulkLabels(Collection $orderItems)
    {
        $pdf = Pdf::loadView('pdfs.bulk-item-labels', ['orderItems' => $orderItems]);
        $pdf->setPaper('A6', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'bulk_labels.pdf');
    }

}
