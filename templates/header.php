<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="sl" class="has-navbar-fixed-top">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sklepnik - ZTS glasuje online</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
    <link rel='stylesheet' href='app.css?<?php echo filemtime("app.css"); ?>'/>
    <link href="assets/css/all.css" rel="stylesheet">

    <script type="text/javascript" src="js/funkcije.js"></script>
    <script type="text/javascript" src="js/sklepnik.js?<?php echo filemtime("sklepnik.js"); ?>"></script>
</head>
<body>

<nav class="navbar is-fixed-top is-primary has-background-primary" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item">
            <b><?php echo $dogodek_naslov; ?></b>
        </a>
        <a role="button" class="navbar-burger" data-target="navMenu" aria-label="menu" aria-expanded="false">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
            <span class="navbar-item">Delegat: <?php echo $uporabnik; ?></span>
        </div>

        <div class="navbar-end">

            <br/>

            <a class="navbar-item" target="_blank" href="rezultati.php?dogodek=<?php $dogodek->access_key?>">Rezultati</a>
            <a class="navbar-item" target="_blank" href="https://www.google.com">Gradivo</a>

            <br/>

            <!-- ikone za sklepcnost-->
            <div class="navbar-item is-hidden" id="quorum-status-ok">
                <span class="icon" title="Skupščina je sklepčna">
                    <i class="fas fa-users"></i>
                </span>
                <span class="is-hidden-desktop">Skupščina je sklepčna</span>
            </div>

            <div class="navbar-item is-hidden" id="quorum-status-error" >
                <span class="icon has-text-danger" title="Skupščina NI sklepčna">
                    <i class="fas fa-users-slash"></i>
                </span>
                <span class="is-hidden-desktop">Skupščina NI sklepčna</span>
            </div>

            <div class="navbar-item" id="quorum-status-wait" >
                <span class="icon" title="Ni podatka o sklepčnosti">
                    <i class="fas fa-spinner fa-pulse"></i>
                </span>
                <span class="is-hidden-desktop">Ni podatka o sklepčnosti</span>
            </div>

            <!-- ikone za povezavo-->
            <div class="navbar-item is-hidden" id="conn-status-ok" >
                <span class="icon" title="Povezava s sistemom je aktivna">
                    <i class="fas fa-bolt"></i>
                </span>
                <span class="is-hidden-desktop">Povezava s sistemom je aktivna</span>
            </div>

            <div class="navbar-item is-hidden" id="conn-status-error" >
                <span class="icon has-text-danger" title="Povezava s sistemom ne deluje">
                    <span class="fa-stack">
                      <i class="fas fa-bolt fa-stack-1x"></i>
                      <i class="fas fa-slash fa-stack-1x"></i>
                    </span>
                </span>
                <span class="is-hidden-desktop">Povezava s sistemom ne deluje</span>
            </div>

            <div class="navbar-item is-hidden" id="conn-status-wait" >
                <span class="icon" title="Vzpostavljam povezavo...">
                    <i class="fas fa-spinner fa-pulse"></i>
                </span>
                <span class="is-hidden-desktop">Vzpostavljam povezavo s sistemom...</span>
            </div>
        </div>
    </div>
</nav>
