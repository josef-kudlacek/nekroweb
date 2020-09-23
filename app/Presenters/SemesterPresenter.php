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

    public function renderShow()
    {
        $this->template->semesters = $this->semester->getSemesters();
    }

    public function actionEdit($semesterId)
    {
        $semester = $this->semester->GetSemesterById($semesterId)->fetch();
        if (!$semester) {
            $this->flashMessage('Semestr nenalezen.', "danger");;
            $this->redirect('Semester:show');
        }

        $this['semesterForm']->setDefaults([
            'YearFrom' => $semester->YearFrom,
            'YearTo' => $semester->YearTo
        ]);
    }

    protected function createComponentSemesterForm(): Form
    {
        $form = new Form;

        $form->addText('YearFrom')
            ->setRequired('Prosím vyplňte začátek.')
            ->setMaxLength(4)
            ->addRule($form::INTEGER, 'Začátek semestru musí být číslo')
            ->addRule($form::LENGTH, 'Konec semestru musí mít %d čísel', 4);

        $form->addText('YearTo')
            ->setMaxLength(4);

        $form->addSubmit('send');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'semesterFormSucceeded'];

        return $form;
    }

    public function semesterFormSucceeded(Form $form, \stdClass $values): void
    {
        $semesterId = $this->getParameter('semesterId');
        $values = Utils::convertEmptyToNull($form->getValues());

        if ($semesterId) {
            $this->semester->updateSemester($values, $semesterId);
            $this->flashMessage('Semestr '.
                $values->YearFrom . '/.' . $values->YearFrom . ' úspěšně upraven.', 'success');
        } else {
            $this->semester->insertSemester($values);
            $this->flashMessage('Semestr '.
                $values->YearFrom . '/.' . $values->YearFrom . ' úspěšně vložen.', 'success');
        }

        $this->redirect('Semester:show');
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