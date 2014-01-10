BtnNodesBundle
==============

sample cms structure tree for menus

=============

### Step 1: Add MessageBundle in your composer.json (private repo)

```js
{
    "require": {
        "bitnoise/message-bundle": "dev-master",
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "git@github.com:Bitnoise/BtnMessageBundle.git"
        }
    ],
}
```

### Step 2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Btn\MessageBundle\BtnMessageBundle(),
    );
}
```

### Step 3: Create Thread Entity

``` php
<?php

namespace App\ControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Btn\MessageBundle\Entity\Thread as BaseThread;

/**
 * Thread
 *
 * @ORM\Entity
 * @ORM\Table(name="thread")
 */
class Thread extends BaseThread
{
    /**
     *
     * @ORM\ManyToMany(targetEntity="User")
     */
    protected $participants;
}
```

### Step 4: Create Message Entity

``` php
<?php

namespace App\ControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Btn\MessageBundle\Entity\Message as BaseMessage;

/**
 * Message
 *
 * @ORM\Table(name="message")
 * @ORM\Entity()
 */
class Message extends BaseMessage
{
    /**
     * @var \App\ControlBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $sender;

    /**
     * @var \App\ControlBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $recipient;
}

```

### Step 5: Setup config

``` yml
# app/config/config/config.yml
# ...
# Btn message configuration
btn_message:
    thread_class: App\ControlBundle\Entity\Thread
    message_class: App\ControlBundle\Entity\Message
    # custom thread manager (optional)
    thread_manager: app.thread_manager   
    # custom message manager (optional)
    message_manager: app.message_manager 
    # message types (optional)
    message_type:            
        message:
            id: 1
            name: "message"
        meeting:
            id: 2
            name: "meeting"
        reservation:
            id: 3
            name: "reservation"
```

### Step 6: Update your database schema

``` bash
$ php app/console doctrine:schema:update --force
```
