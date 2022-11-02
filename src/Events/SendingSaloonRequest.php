<?php declare(strict_types=1);

namespace Sammyjo20\SaloonLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
