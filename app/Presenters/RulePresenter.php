<?php


namespace App\Presenters;

use App\Model;


class RulePresenter extends BasePresenter
{
    private $rule;

    public function __construct(Model\Rule $rule)
    {
        $this->rule = $rule;
    }

    public function renderShow()
    {
        $this->template->rules = $this->rule->GetRules();
    }
}