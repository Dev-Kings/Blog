<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\BooleanColumn;
use App\Filament\Resources\PostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\PostResource\RelationManagers\TagsRelationManager;
use App\Filament\Resources\PostResource\Widgets\StatsOverview;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Select::make('category_id')
                        ->relationship('category', 'name'),
                    TextInput::make('title')->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        })->required(),
                    TextInput::make('slug')->required(),
                    SpatieMediaLibraryFileUpload::make('thumbnail')->collection('posts'),
                    RichEditor::make('content'),
                    Toggle::make('is_published')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->limit(50)->sortable()->searchable(),
                TextColumn::make('slug')->limit(50),
                BooleanColumn::make('is_published'),
                SpatieMediaLibraryImageColumn::make('thumbnail')->collection('posts')
            ])
            ->filters([
                Filter::make('Published')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)),
                    Filter::make('Not published')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', false)),
                    SelectFilter::make('category')->relationship('category', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TagsRelationManager::class
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOverview::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
