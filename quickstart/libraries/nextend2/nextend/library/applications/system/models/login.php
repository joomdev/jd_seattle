<?php

class N2SystemLoginModel extends N2Model {

    public static function renderForm() {

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('system')
                                 ->getApplicationType('backend'));


        $loginTab = new N2Tab($form, 'login', n2_('Login'));
        new N2ElementText($loginTab, 'user_name', n2_('User name'), '', array(
            'style' => 'width:200px;'
        ));
        new N2ElementPassword($loginTab, 'user_password', n2_('Password'), '', array(
            'style' => 'width:200px;'
        ));

        new N2ElementToken($loginTab);

        return $form->render('login');
    }
}