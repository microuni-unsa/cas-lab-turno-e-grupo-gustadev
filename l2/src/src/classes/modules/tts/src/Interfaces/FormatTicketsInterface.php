<?php

namespace idoit\Module\Tts\Interfaces;

interface FormatTicketsInterface
{
    /**
     * @param array $objectTicekts
     *
     * @return array
     */
    public function formatTickets(array $objectTicekts): array;
}
