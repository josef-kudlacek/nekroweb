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
        $this->template->characters = $this->isLogged();
    }

    private function isLogged()
    {
        parent::startup();
        if ($this->getUser()->loggedIn) {
            $userIdentity = $this->getUser()->getIdentity();

            return $this->character->getCharactersByClassId($userIdentity->classId);
        } else {
            return $this->character->getCharacters();
        }
    }
}