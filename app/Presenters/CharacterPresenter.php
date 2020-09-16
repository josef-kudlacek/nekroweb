<?php


namespace App\Presenters;

use App\Model;


class CharacterPresenter extends BasePresenter
{
    private $character;

    public function __construct(Model\Character $character)
    {
        $this->character = $character;
    }

    public function renderShow()
    {
        $this->template->characters = $this->character->getCharacters();
    }
}