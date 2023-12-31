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
        $this->template->characters = $this->getCharacterData();
    }

    private function getCharacterData()
    {
        parent::startup();
        if ($this->getUser()->loggedIn && !$this->getUser()->isInRole('Profesor')) {
            $userIdentity = $this->getUser()->getIdentity();

            return $this->character->getCharactersByClassId($userIdentity->classId);
        } else {
            return $this->character->getCharacters();
        }
    }
}