<?php
/**
 * Class IndexController
 *
 * @category TheiaLive
 * @package Default
 */
class IndexController extends Zend_Controller_Action
{
    protected $_session;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace('contact');
    }

    public function indexAction()
    {
        // action body
    }

    public function contactAction()
    {
        $form = new Application_Form_Contact([
            'method' => 'POST',
            'action' => $this->view->url([
                'module' => 'default',
                'controller' => 'index',
                'action' => 'submit',
            ], null, true),
        ]);

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $account = unserialize($identity);
            $form->getElement('name')->setValue($account->getFirstName() . ' ' . $account->getLastName());
            $form->getElement('email')->setValue($account->getEmail());
        }

        if (isset($this->_session->contactForm)) {
            $form = unserialize($this->_session->contactForm);
            unset($this->_session->contactForm);
        }

        $this->view->contactForm = $form;
    }

    public function submitAction()
    {
        if (! $this->getRequest()->isPost()) {
            return $this->_helper->redirector('contact', 'index', 'default');
        }
        $form = new Application_Form_Contact([
            'method' => 'POST',
            'action' => $this->view->url([
                'module' => 'default',
                'controller' => 'index',
                'action' => 'submit',
            ], null, true),
        ]);
        if (! $form->isValid($this->getRequest()->getPost())) {
            $this->_session->contactForm = serialize($form);
            return $this->_helper->redirector('contact', 'index', 'default');
        }

        $html = file_get_contents(APPLICATION_PATH . "/templates/contact.html");
        $text = file_get_contents(APPLICATION_PATH . "/templates/contact.txt");

        $html = str_replace('{{MSG}}', $form->getElement('comment')->getValue(), $html);
        $text = str_replace('{{MSG}}', $form->getElement('comment')->getValue(), $text);

        // send message
        $mail = new Zend_Mail();
        $mail->setFrom($form->getElement('email')->getValue(), $form->getElement('name')->getValue());
        $mail->addTo('info@in2it.be');
        $mail->setSubject('Contact request from TheiaLive');
        $mail->setBodyHtml($html);
        $mail->setBodyText($text);
        $mail->send();

        return $this->_helper->redirector('success', 'index', 'default');
    }

    public function successAction()
    {
        // action body
    }
}
