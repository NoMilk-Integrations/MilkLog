<?php

namespace RootAccessPlease\MilkLog\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RootAccessPlease\MilkLog\Contracts\MilkLogInterface;
use RootAccessPlease\MilkLog\Notifications\SlackNotification;

class MilkLogService implements MilkLogInterface
{
    protected ?string $title = null;
    protected string $level = '';
    protected string $message = '';
    protected array $context = [];
    protected ?string $slackChannel = null;
    protected array $slackTags = [];
    protected ?string $logChannel = null;

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function alert(string $message, array $context = []): self
    {
        return $this->log('alert', $message, $context);
    }

    public function error(string $message, array $context = []): self
    {
        return $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): self
    {
        return $this->log('warning', $message, $context);
    }
    public function info(string $message, array $context = []): self
    {
        return $this->log('info', $message, $context);
    }

    public function channel(string $channel): self
    {
        $this->slackChannel = $channel;

        return $this;
    }

    public function tags(array $tags): self
    {
        $this->slackTags = $tags;

        return $this;
    }

    public function logChannel(string $channel): self
    {
        $this->logChannel = $channel;

        return $this;
    }

    public function inform(): void
    {
        $this->writeToLog();
        $this->sendSlackNotification();
        $this->reset();
    }

    protected function log(string $level, string $message, array $context = []): self
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;

        return $this;
    }

    protected function writeToLog(): void
    {
        if (empty($this->level) || empty($this->message)) {
            return;
        }

        $logger = $this->logChannel
            ? Log::channel($this->logChannel)
            : Log::channel(config('milklog.logging.channel'));

        $context = $this->buildLogContext();

        $logger->{$this->level}($this->message, $context);
    }

    protected function sendSlackNotification(): void
    {
        if (! $this->shouldSendNotification()) {
            return;
        }

        $notification = new SlackNotification(
            $this->title,
            $this->level,
            $this->message,
            $this->context,
            $this->getSlackOptions()
        );

        Notification::route('slack', config('milklog.slack.channel'))->notify($notification);
    }

    protected function shouldSendNotification(): bool
    {
        if (! config('milklog.notifications.enabled', true)) {
            return false;
        }

        return true;
    }

    protected function getSlackOptions(): array
    {
        return [
            'channel' => $this->slackChannel ?? config('milklog.slack.channel'),
            'tags' => ! empty($this->slackTags) ? $this->slackTags : config('milklog.slack.tags', []),
        ];
    }

    protected function buildLogContext(): array
    {
        $context = $this->context;

        if (config('milklog.logging.include_context', true)) {
            $context['milklog'] = [
                'timestamp' => now()->toISOString(),
                'level' => $this->level,
                'slack_channel' => $this->slackChannel ?? config('milklog.slack.channel'),
                'tags' => ! empty($this->slackTags) ? $this->slackTags : config('milklog.slack.tags', []),
            ];
        }

        if (config('milklog.logging.include_trace', false) && in_array($this->level, ['error', 'alert'])) {
            $context['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        return $context;
    }

    protected function reset(): void
    {
        $this->level = '';
        $this->message = '';
        $this->context = [];
        $this->slackChannel = null;
        $this->slackTags = [];
        $this->logChannel = null;
    }
}