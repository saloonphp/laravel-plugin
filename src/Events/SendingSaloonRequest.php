<?php declare(strict_types=1);

namespace Saloon\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Saloon\Http\PendingSaloonRequest;

class SendingSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing SaloonRequest
     *
     * @var PendingSaloonRequest
     */
    public PendingSaloonRequest $pendingRequest;

    /**
     * Create a new event instance.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function __construct(PendingSaloonRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;
    }
}
