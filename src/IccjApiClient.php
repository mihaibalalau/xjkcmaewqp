<?php

namespace Mihaib\IccjApiClient;

use GuzzleHttp\Client;
use stdClass;
use Mihaib\IccjApiClient\Dosar\DosarIccjFactory;
use Mihaib\IccjApiClient\Dosar\GetDosareIccjQuery;
use Mihaib\IccjApiClient\Dosar\Entities\DosarIccjCollection;
use Mihaib\IccjApiClient\Exceptions\InvalidResponseException;

class IccjApiClient
{
    const CAUTARE_DOSARE = 'CautareDosare';
    const CAUTARE_SEDINTE = 'CautareSedinte';

    private Client $client;

    public function __construct(string $url)
    {
        $this->client = new Client(['base_uri' => $url]);
    }

    public function getDosare(GetDosareIccjQuery $query): DosarIccjCollection
    {
        $query->validate();

        $response = $this->request(self::CAUTARE_DOSARE, $query->toArray());

        return new DosarIccjCollection(array_map(fn (stdClass $data) => DosarIccjFactory::fromObject($data), $response));
    }
    /**
     * @return stdClass[]
     */
    protected function request(string $path, array $query): array
    {
        $response = $this->client->get($path, [
            'query' => $query
        ]);

        $body = $response->getBody()->getContents();
        $json = json_decode($body);

        if (is_null($json)) {
            throw new InvalidResponseException("Could not decode JSON reponse from Iccj: $body");
        }

        return $json;
    }
}
