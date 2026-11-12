<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatAutoReplyResource\Pages;
use App\Models\ChatAutoReply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChatAutoReplyResource extends Resource
{
    protected static ?string $model = ChatAutoReply::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Trả lời tự động';
    protected static ?string $modelLabel = 'Mẫu trả lời';
    protected static ?string $pluralModelLabel = 'Mẫu trả lời tự động';
    protected static ?string $navigationGroup = 'Quản lý khách hàng';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin mẫu trả lời')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Mã mẫu')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Ví dụ: SHIP_FEE. Mã dùng nội bộ, không hiển thị cho khách.'),

                        Forms\Components\Select::make('group_name')
                            ->label('Nhóm chủ đề')
                            ->options(ChatAutoReply::groupLabels())
                            ->required()
                            ->default('general'),

                        Forms\Components\TextInput::make('question')
                            ->label('Câu hỏi mẫu (hiển thị cho khách chọn)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('keywords')
                            ->label('Từ khóa nhận diện')
                            ->placeholder('Nhập từ khóa rồi nhấn Enter')
                            ->helperText('Càng nhiều từ khóa đặc thù, độ chính xác càng cao. Không cần gõ dấu.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('answer')
                            ->label('Nội dung trả lời tự động')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('priority')
                            ->label('Độ ưu tiên')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Từ 0 đến 100. Khi nhiều mẫu cùng điểm khớp, mẫu có độ ưu tiên lớn hơn sẽ được chọn.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Đang bật')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Câu hỏi mẫu')
                    ->searchable()
                    ->wrap()
                    ->limit(70),

                Tables\Columns\TextColumn::make('group_name')
                    ->label('Nhóm')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ChatAutoReply::groupLabels()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('hit_count')
                    ->label('Lượt dùng')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Ưu tiên')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Bật')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_name')
                    ->label('Nhóm chủ đề')
                    ->options(ChatAutoReply::groupLabels()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang bật')
                    ->falseLabel('Đã tắt'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatAutoReplies::route('/'),
            'create' => Pages\CreateChatAutoReply::route('/create'),
            'edit' => Pages\EditChatAutoReply::route('/{record}/edit'),
        ];
    }
}
