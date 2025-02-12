<?php

namespace App\Admin\Controllers;

use App\Model\MerchantEnteringAgreement;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class MerchantEnteringAgreementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '入住协议';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MerchantEnteringAgreement());

        $grid->column('id', __('Id'));
        $grid->column('content', __('Content'))->display(function ($content) {
            return Str::limit($content, 50, '....');
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MerchantEnteringAgreement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('content', __('Content'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MerchantEnteringAgreement());

        $form->UEditor('content', __('Content'));
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
