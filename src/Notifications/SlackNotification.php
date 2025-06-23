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
    protected array $properties;
    protected array $slackOptions;

    public function __construct(
        ?string $title,
        string $level,
        string $message,
        array $properties = [],
        array $slackOptions = []
    ) {
        $this->title = $title;
        $this->level = $level;
        $this->message = $message;
        $this->properties = $properties;
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
        $sectionFields = [];

        foreach ($this->properties as $key => $value) {
            $sectionFields[] = [
                'title' => $key,
                'value' => $value
            ];
        }

        return array_merge(
            $sectionFields,
            $this->getEnvironmentFields()
        );
    }

    protected function getEnvironmentFields(): array
    {
        return [
            ['title' => 'Environment', 'value' => app()->environment()],
            ['title' => 'Application', 'value' => config('app.name')],
        ];
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