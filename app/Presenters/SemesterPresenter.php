<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

class SemesterPresenter extends BasePresenter
{
    private $semester;

    public function __construct(Model\Semester $semester)
    {
        $this->semester = $semester;
    }

    protected function createComponentChangeSemesterForm(): Form
    {
        $form = new Form;

        $this->template->semesters = $this->semester->getSemesters();

        $selectItems = Utils::prepareSemesterSelectBoxArray($this->template->semesters);

        $form->addSelect('semester')
            ->setItems($selectItems)
            ->setRequired();

        $form->addSubmit('send', 'Přepnout semestr');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'changeSemesterFormSucceeded'];

        return $form;
    }

    public function changeSemesterFormSucceeded(Form $form, \stdClass $values): void
    {
        $semesters = $this->template->semesters->fetchAll();
        $SemesterId = array_search($values->semester, array_column($semesters, 'SemesterId'));
        $semester = $semesters[$SemesterId];

        $this->user->getIdentity()->semesterFrom = $semester->YearFrom;
        $this->user->getIdentity()->semesterTo = $semester->YearTo;
        $this->user->getIdentity()->semesterId = $semester->SemesterId;
        $this->flashMessage('Semestr úspěšně změněn. Vítej ve školním roce '.
            $semester->YearFrom . '/.' . $semester->YearFrom . '!' ,"success");
        $this->redirect('Homepage:default');
    }


}