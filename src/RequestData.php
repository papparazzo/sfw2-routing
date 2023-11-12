<?php

namespace SFW2\Routing;

class RequestData
{
    public const IS_HOME = 'is_home';

    public const ACTION = 'action';

    public const PATH_ID = 'path_id';

    public const PATH_SIMPLIFIED = 'path_simplified';

    public const PATH = 'path';


    #public bool $authenticated;

    # sitemapdata    => $this->menu->getFullMenu());
    # main_menu       => $this->menu->getMainMenu());
    # side_menu       => $this->menu->getSideMenu());
    # authenticated  => (bool)$this->session->getGlobalEntry(User::class));

    # created        => creationDate
    # updated        => modificationDate
    # currentPath

    # path_simplified
    # token (xsrf?) / exception? $exc->getIdentifier()
    # id' => $request->getPathSimplified(),
    # permission => 'permission', # $this->pagePermission->getPermissionArray(),
    # error => $this->result->hasErrors(),
    # data' => $this->result->getData()
/*
     $this->assignArray([
        'title' => $exc->getTitle(),
        'caption' => $exc->getCaption(),
        'description' => $exc->getDescription(),
    ]);
*/
        #$title =
        #    $this->config->get('project.title') . ' - ' .
        #    $this->result->getValue('title', '');

        #$view->assign('title', trim($title, ' -'));

/*


    protected function showContent(): void
    {
        if (!isset($this->vars['modificationDate']) || $this->vars['modificationDate'] == '') {
            $this->vars['modificationDate'] = new DateTime('@' . filemtime($this->template), new DateTimeZone('Europe/Berlin'));
        }

        if (is_string($this->vars['modificationDate'])) {
            $this->vars['modificationDate'] = new DateTime($this->vars['modificationDate'], new DateTimeZone('Europe/Berlin'));
        }

        #Mi., 11. Mai. 2016
        $this->vars['modificationDate'] = strftime('%a., %d. %b. %Y', $this->vars['modificationDate']->getTimestamp());
    }*/
}
