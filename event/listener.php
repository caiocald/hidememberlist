<?php
namespace red1000\hidememberlist\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\controller\helper;
use function redirect;
use function append_sid;

class listener implements EventSubscriberInterface
{
    protected $auth;
    protected $request;
    protected $helper;
    protected $root_path;
    protected $php_ext;

    public function __construct(
        \phpbb\auth\auth $auth,
        \phpbb\request\request_interface $request,
        helper $helper,
        $root_path,
        $php_ext
    ) {
        $this->auth      = $auth;
        $this->request   = $request;
        $this->helper    = $helper;
        $this->root_path = $root_path;
        $this->php_ext   = $php_ext;
    }

    public static function getSubscribedEvents()
    {
        return ['core.page_header' => 'deny_memberlist'];
    }

    public function deny_memberlist()
    {
        // só não bloqueia administradores
        if ($this->auth->acl_get('a_')) {
            return;
        }

        $mode   = $this->request->variable('mode', '');
        $script = basename($this->request->server('PHP_SELF', ''));

        $is_memberlist = $mode === 'memberlist' || $script === 'memberlist.php';
        if (! $is_memberlist) {
            return;
        }

        // se não for viewprofile, redireciona de volta ao índice
        if ($mode !== 'viewprofile') {
            redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
        }
    }
}
