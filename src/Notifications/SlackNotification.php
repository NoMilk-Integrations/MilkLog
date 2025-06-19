<?php

namespace RootAccessPlease\MilkLog\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;

class SlackNotification extends Notification
{
    protected ?string $title = null;
    protected string $level;
    protected string $message;
    protected array $context;
    protected array $slackOptions;

    public function __construct(
        ?string $title,
        string $level,
        string $message,
        array $context = [],
        array $slackOptions = []
    ) {
        $this->title = $title;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->slackOptions = $slackOptions;
    }

    public function via(object $notifiable): array
    {
        return ['slack'];
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->to($this->slackOptions['channel'] ?? '#general')
            ->sectionBlock(function (SectionBlock $block) {
                $block->text("{$this->getEmojiForLevel()} *{$this->getTitle()}*\n\n*Message:*\n{$this->message}")->markdown();
                foreach ($this->buildFieldsForSection() as $field) {
                    $block->field("\n>*{$field['title']}:*\n>{$field['value']}")->markdown();
                }
            })
            ->when(! empty($this->slackOptions['tags']), function (SlackMessage $message) {
                foreach ($this->slackOptions['tags'] as $tag) {
                    $message->sectionBlock(fn (SectionBlock $block) => $block->text("<@$tag>")->markdown());
                }
            });
    }

    protected function buildFieldsForSection(): array
    {
        $fields = [
            ['title' => 'Environment', 'value' => app()->environment()],
            ['title' => 'Application', 'value' => config('app.name')],
        ];

        $extra = Arr::except($this->context, ['milklog', 'trace']);

        if ($extra) {
            $fields[] = [
                'title' => 'Context',
                'value' => $this->formatContext($extra),
            ];
        }

        return $fields;
    }

    protected function formatContext(array $context): string
    {
        $lines = [];

        foreach ($context as $key => $value) {
            $lines[] = is_array($value) || is_object($value)
                ? "*{$key}*: ```".json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."```"
                : "*{$key}*: {$value}";
        }

        return implode("\n", $lines);
    }

    protected function getTitle(): string
    {
        return $this->title ?? $this->getTitelForLevel();
    }

    protected function getTitelForLevel(): string
    {
        return match ($this->level) {
            'alert' => 'Alarm!',
            'error' => 'Fejl!',
            'warning' => 'Advarsel!',
            default => 'Information',
        };
    }

    protected function getEmojiForLevel(): string
    {
        return match ($this->level) {
            'alert' => ':rotating_light:',
            'error' => ':red_circle:',
            'warning' => ':large_orange_circle:',
            default => ':large_blue_circle:',
        };
    }

    protected function getFormattedLevel(): string
    {
        return ucfirst($this->level);
    }
}