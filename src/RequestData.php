<?php

namespace SFW2\Routing;

// TODO: Rename class
// TODO: make this a enum
class RequestData
{
    public const string IS_HOME = 'is_home';

    public const string ACTION = 'action';

    public const string PATH_SIMPLIFIED = 'path_simplified';

    public const string PATH = 'path';

    # created        => creationDate
    # updated        => modificationDate
    # currentPath

    # token (xsrf?) / exception? $exc->getIdentifier()
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
