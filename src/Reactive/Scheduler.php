<?php

namespace Onion\Framework\Reactive;

use function Onion\Framework\Loop\coroutine;
use Onion\Framework\Loop\Coroutine;
use Onion\Framework\Loop\Timer;
use Rx\AsyncSchedulerInterface;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;

class Scheduler implements AsyncSchedulerInterface
{
    public static function factory()
    {
        return new self;
    }

    public function schedule(callable $action, $delay = 0): \Rx\DisposableInterface
    {
        $disposable = new CallbackDisposable(function () use ($action) {
            call_user_func($action, $this);
        });

        coroutine(function ($disposable, $delay) {
            yield Timer::after(function (DisposableInterface $disposable) {
                yield $disposable->dispose();
            }, $delay, [$disposable]);
        }, [$disposable, $delay]);

        return $disposable;
    }

    public function scheduleRecursive(callable $action): \Rx\DisposableInterface
    {
        $disposable = new SerialDisposable();
        $recursiveAction = null;
        $recursiveAction = function () use ($action, &$disposable, &$recursiveAction) {
            $disposable->setDisposable($this->schedule(function () use ($action, &$recursiveAction) {
                $action(function () use (&$recursiveAction) {
                    $recursiveAction();
                });
            }));
        };

        $recursiveAction();

        return $disposable;
    }

    public function schedulePeriodic(callable $action, $delay, $period): \Rx\DisposableInterface
    {
        $disposable = new SerialDisposable;

        coroutine(function($action, $delay, $period) use (&$disposable) {
            $timer = null;
            $timer = yield Timer::interval(function (callable $action, int $delay) use (&$timer, &$disposable) {
                yield $disposable->setDisposable($this->schedule($action, $delay));

                $disposable->setDisposable(new CallbackDisposable(function () use (&$disposable, &$timer) {
                    if ($disposable->getDisposable() === null) {
                        coroutine(function (int $timer) {
                            yield Coroutine::kill($timer);
                        }, [$timer]);
                    }
                }));
            }, $period, [$action, $delay]);

        }, [$action, $delay, $period]);

        return $disposable;
    }

    public function now(): int
    {
        return time();
    }
}
