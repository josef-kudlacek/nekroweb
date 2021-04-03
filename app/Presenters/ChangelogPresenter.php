<?php


namespace App\Presenters;


use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class ChangelogPresenter extends BasePresenter
{
    private $changelog;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\ChangeLog $changelog)
    {
        $this->changelog = $changelog;
    }

    public function renderShow()
    {
        $this->template->changelog = $this->changelog->getChangelog();
    }

    public function renderAdmin()
    {
        $this->checkRole();

        $this->template->changelog = $this->changelog->getChangelog();
    }

    public function renderCreate()
    {
        $this->checkRole();
    }

    public function renderEdit($itemId)
    {
        $changeLogItem = $this->checkAccess($itemId);

        $this['changelogForm']->setDefaults($changeLogItem);
    }

    public function actionDelete($itemId)
    {
        $this->checkAccess($itemId);

        $this->transaction->startTransaction();
        $this->changelog->deleteChangelogItem($itemId);
        $this->transaction->endTransaction();

        $this->flashMessage('Záznam úspěšně smazán.','success');
        $this->redirect('Changelog:admin');
    }

    protected function createComponentChangelogForm(): Form
    {
        $form = new Form;

        $form->addText('Id');

        $form->addText('Version')
            ->setRequired('Vyplňte prosím číslo verze.')
            ->setMaxLength(5);

        $form->addTextArea('Description')
            ->setRequired('Vyplňte prosím popis verze.');

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'changelogFormSucceeded'];

        return $form;
    }

    public function changelogFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = Utils::convertEmptyToNull($form->values);
        $values->Datetime = date('Y-m-d H:i:s');

        $this->transaction->startTransaction();

        if ($values->Id)
        {
            $this->changelog->updateChangelogItem($values);

            $this->flashMessage('Záznam úspěšně upraven.','success');
        } else {
            $this->changelog->insertChangelogItem($values);

            $this->flashMessage('Záznam úspěšně přidán.','success');
        }

        $this->transaction->endTransaction();

        $this->redirect('Changelog:admin');
    }

    private function checkAccess($itemId)
    {
        $this->checkRole();

        $changeLogItem = $this->changelog->getChangelogItemById($itemId)->fetch();

        if(!$changeLogItem)
        {
            $this->flashMessage('Záznam nenalezen.','danger');
            $this->redirect('Changelog:admin');
        }

        return $changeLogItem;
    }

    private function checkRole()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

}