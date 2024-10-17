<?php
declare(strict_types=1);

namespace Vonage\VonagePhpServerless\Traits;

use Vonage\Client;

trait VonageClientAware
{
    protected Client $client;

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}