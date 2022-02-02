<?php
namespace Webster;

/**
 * EtcHosts manager
 */
class EtcHostsManager
{
    private $items = [];
    private $contents = '';
    private $tmpEtcHostsFilepath;

    const ITEM_TYPE_DOMAIN = 'DOMAIN';
    const ITEM_TYPE_EMPTY = 'EMPTY';
    const ITEM_TYPE_COMMENT = 'COMMENT';
    const ITEM_TYPE_DOMAINMULTIPLE = 'DOMAINMULTIPLE';

    /**
     * EtcHostsManager constructor.
     * @param string $etcHostsPath
     */
    public function __construct($tmpEtcHostsFilepath)
    {
        $this->tmpEtcHostsFilepath = $tmpEtcHostsFilepath;

        $this->contents = file_get_contents('/etc/hosts');
        $this->parse();
    }


    // PUBLIC METHODS

    /**
     * Adds new domain to items
     *
     * @param $domain
     * @param string $ip
     */
    public function add($domain, $ip = '127.0.0.1')
    {
        $item = [
            'type'      => self::ITEM_TYPE_DOMAIN,
            'num'       => count($this->items)-2,
            'key'       => $domain,
            'value'     => $ip,
        ];

        // insert item before last end-of-line-item
        array_splice($this->items, count($this->items)-2, 0, [$item]);
    }

    /**
     * Returns array of items
     *
     * @return array
     */
    public function getAll()
    {
        //return array_slice($this->items, 10, 10);
        return $this->items;
    }

    /**
     * Saves original contents of hosts file to specified path
     *
     * @param string $filepath
     */
    public function backup($filepath='~/Webster/hosts')
    {
        $filepath = expand_tilde($filepath);
        file_put_contents($filepath, $this->contents);
    }

    /**
     * Saves items as file to specified path
     *
     * @param string $filepath
     */
    public function save($filepath = '/etc/hosts')
    {
        $this->cleanEOLs();

        $output = '';
        foreach ($this->items as $item) {
            if ($item['type'] != self::ITEM_TYPE_DOMAIN) {
                $output .= "{$item['value']}\n";
            }
            else {
                $output .= "{$item['value']}\t\t{$item['key']}\n";
            }
        }
        file_put_contents($this->tmpEtcHostsFilepath, $output);

        $shCmd = "sudo mv -f {$this->tmpEtcHostsFilepath} {$filepath}";
        if (FALSE === shell_exec($shCmd)) {
            die('Could not write to /etc/hosts' . PHP_EOL);
        }
    }

    /**
     * Deletes item by name
     *
     * @param $domain_name
     * @return bool
     */
    public function remove($domain_name)
    {
        $idx = array_search($domain_name, array_column($this->items, 'key'));
        if (FALSE !== $idx) {
            unset($this->items[$idx]);
            return TRUE;
        }
        return FALSE;
    }


    // PRIVATES

    /**
     * Parses input file into items, where:
     *   item = [type => '', num => '', key => '', value => '']
     */
    private function parse()
    {
        $lines = explode("\n", $this->contents);
        $uniqitems = [];

        foreach ($lines as $n => $L) {
            $line = trim($L);
            if (!$line) {
                $item = [
                    'type'      => self::ITEM_TYPE_EMPTY,
                    'num'       => $n,
                    'key'       => null,
                    'value'     => null,
                ];
            }
            else {
                if (@$line{0} === '#') { // if comment line
                    $item = [
                        'type'      => self::ITEM_TYPE_COMMENT,
                        'num'       => $n,
                        'key'       => null,
                        'value'     => $line,
                    ];
                }
                else { // if not comment line
                    $parts = preg_split('/[ \t]+/', $line, 2);
                    $ip = @$parts[0];
                    if (preg_match('/[ \t]+/', @$parts[1])) {
                        $item = [
                            'type'      => self::ITEM_TYPE_DOMAINMULTIPLE,
                            'num'       => $n,
                            'key'       => '',
                            'value'     => $line,
                        ];
                    }
                    else { // if not multiple domains
                        $item = [
                            'type'      => self::ITEM_TYPE_EMPTY,
                            'num'       => $n,
                            'key'       => null,
                            'value'     => null,
                        ];


                        $domain = @$parts[1];
                        if (!array_key_exists($domain, $uniqitems)) { // add only unique domain names
                            $uniqitems[$domain] = 1;
                            $item = [
                                'type'      => self::ITEM_TYPE_DOMAIN,
                                'num'       => $n,
                                'key'       => $domain,
                                'value'     => $ip,
                            ];
                        }
                    }
                }
            }

            $this->items[] = $item;
        }
    }

    /**
     * Remove all EOL items at the end of items array.
     * This is called before body of 'save' method is executed.
     */
    private function cleanEOLs()
    {
        $last_type = self::ITEM_TYPE_EMPTY;
        $len = count($this->items);
        $i = $len-1;
        do {
            $item = $this->items[$i];
            $type = $item['type'];
            if ($type == self::ITEM_TYPE_EMPTY && $last_type == self::ITEM_TYPE_EMPTY) {
                array_splice($this->items, $i, 1);
                $i--;
            }
            else {
                break;
            }
            $i--;
        } while ($i > 0 && $last_type == self::ITEM_TYPE_EMPTY);
    }

}

// usage
//$ehm = new EtcHostsManager();
//$ehm->add($domain_name, $ip);

// remove site
//$ehm->delete($domain_name);

// return array of sites
//var_dump($ehm->getAll());
//$ehm->backup();
//$ehm->add('laratestsite.loc');
//$ehm->add('homie.loc', '127.0.0.1');
//$ehm->delete('mxtest.loc');
//$ehm->save('/home/bayeer/Temp/hosts2');
