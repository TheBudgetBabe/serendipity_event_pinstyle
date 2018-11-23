<?php

if (IN_serendipity !== true) {
    die ("Don't hack!");
}

// Probe for a language include with constants. Still include defines later on, if some constants were missing
$probelang = dirname(__FILE__) . '/' . $serendipity['charset'] . 'lang_' . $serendipity['lang'] . '.inc.php';
if (file_exists($probelang)) {
    include $probelang;
}
include dirname(__FILE__) . '/lang_en.inc.php';

class serendipity_event_pinstyle extends serendipity_event
{
    function introspect(&$propbag)
    {
        global $serendipity;

        $propbag->add('name',          PLUGIN_EVENT_PINSTYLE_NAME);
        $propbag->add('description',   PLUGIN_EVENT_PINSTYLE_DESC);
        $propbag->add('stackable',     true);
        $propbag->add('author',        'E Camden Fisher');
        $propbag->add('version',       '0.1');
        $propbag->add('requirements',  array(
            'serendipity' => '2.0',
            'smarty'      => '3.0.0',
            'php'         => '7.0.0'
        ));
        $propbag->add('groups', array('FRONTEND_ENTRY_RELATED'));
        $propbag->add('event_hooks', array( 'entry_display' => true,
                                            'frontend_display' => true,
                                            'genpage' => true
                                            ));

        $conf_array[] = 'entry_limit';
        $conf_array[] = 'row_limit';
        $propbag->add('configuration', $conf_array);
    }

    function introspect_config_item($name, &$propbag) {
        switch($name) {
            case 'entry_limit':
                $propbag->add('name',           PLUGIN_EVENT_PINSTYLE_ENTRY_LIMIT);
                $propbag->add('description',    PLUGIN_EVENT_PINSTYLE_ENTRY_LIMIT_DESC);
                $propbag->add('default',        '20');
                $propbag->add('type',           'string');
                break;
            case 'row_limit':
                $propbag->add('name',           PLUGIN_EVENT_PINSTYLE_ROW_LIMIT);
                $propbag->add('description',    PLUGIN_EVENT_PINSTYLE_ROW_LIMIT_DESC);
                $propbag->add('default',        '2');
                $propbag->add('type',           'string');
                break;
          default:
            return false;
          break;
        }

        return true;
    }

    function generate_content(&$title)
    {
        $title = $this->title;
    }

    function event_hook($event, &$bag, &$eventData, $addData = null)
    {
        global $serendipity;

        $hooks = &$bag->get('event_hooks');

        if (isset($hooks[$event])) {
            switch($event) {
                case 'entry_display':
                    break;
                case 'frontend_display':
                    if ($serendipity['view'] == 'categories') {
                        $imageArr = $this->get_first_image($eventData['body'] . $eventData['extended']);
                        $eventData['cardImageSrc'] = $imageArr['src'];
                        $eventData['cardImageAlt'] = $imageArr['alt'];
                    }
                    break;
                case 'genpage':
                    if ($serendipity['view'] == 'categories') {
                        $serendipity['rowLimit'] = $this->get_config('row_limit');
                        $serendipity["fetchLimit"] = $this->get_config('entry_limit');
                    }
                    break;
                default:
                    return false;

            }
            return true;
        } else {
            return false;
        }
    }

    function get_first_image($html) {
        $image = [
            "alt" => null,
            "image" => null
        ];
        if ($html == '' || sizeof($html) == 0) {
            return $image;
        }

        $doc = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors($internalErrors);

        $tags = $doc->getElementsByTagName('img');
        if (sizeof($tags) == 0 || $tags == null) {
            return $image;
        }

        $image['src'] = $tags[0]->getAttribute('src');
        $image['alt'] = $tags[0]->getAttribute('alt'); 
        return $image;
      }
}

/* vim: set sts=4 ts=4 expandtab : */
?>