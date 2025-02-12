<?php

namespace App\Admin\Controllers;

use App\Model\BannerPostShop;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerPostShopController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '入驻申请轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BannerPostShop());

        $grid->column('id', __('Id'));
        $grid->column('image', __('Image'))->image('',50,50);
        $grid->column('link', __('Link'));
        $grid->column('is_display', __('Is display'));
        $grid->column('sort', __('Sort'));
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
        $show = new Show(BannerPostShop::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('image', __('Image'))->image('',50,50);
        $show->field('link', __('Link'));
        $show->field('is_display', __('Is display'));
        $show->field('sort', __('Sort'));
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
        $form = new Form(new BannerPostShop());

        $form->image('image', __('Image'));
        $form->textarea('link', __('Link'));
        $form->switch('is_display', __('Is display'))->default(1);
        $form->number('sort', __('Sort'))->default(0);

        return $form;
    }
}
