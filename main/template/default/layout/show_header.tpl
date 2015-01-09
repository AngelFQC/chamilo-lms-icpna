<!DOCTYPE html>
<!--[if lt IE 7]> <html lang="{{ document_language }}" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html lang="{{ document_language }}" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html lang="{{ document_language }}" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--><html lang="{{ document_language }}" class="no-js"> <!--<![endif]-->
<head>
    {% include "default/layout/head.tpl" %}
</head>
<body {% if social is not defined %} class="no-background" {% endif %}>
    {% if social is defined %}
{% include "default/layout/main_header.tpl" %}
{#
    show_header and show_footer templates are only called when using the Display::display_header and Display::display_footer
    for backward compatibility we suppose that the default layout is one column which means using a div with class span12
#}
<div id="page-wrap">
    <div class="container">
        <div class="row">
            <div class="span3 {% if lesson_progress_bar %}header-bar" style="display: none;"{% endif %}">
                <div class="bloque-user">
                    <div class="header-logo">
                        <img src="{{ _p.web_css }}nuevo_vlearning/img/logo-vlearning.png">
                    </div>
                {% if social >= 1 %}
                    <div class="profile">
                        <div class="home"><a href="{{ _p.web_modules }}"><img src="{{ _p.web_css }}nuevo_vlearning/img/userlogin/home.png"></a></div>
                        <div class="user-datos">
                            <div class="image-user">
                                <div class="img-user-width">
                                    <img src="{{ _u.avatar}}" class="img-circle">
                                </div>
                            </div>
                            <div class="name">{{_u.firstname}}</div>
                            <div class="lastname">{{_u.lastname}}</div>
                            <div class="cuenta"><a href="{{ _p.web_main }}social/home.php" class="item-cuenta">Mi cuenta</a></div>
                            {{ social_left_content }}
                            <div class="options">
                                <div class="row-fluid">
                                    <div class="span6 help"><a herf="#" data-toggle="modal" data-target="#help"><img src="{{ _p.web_css }}nuevo_vlearning/img/userlogin/icon_help.png">{{"Help"|get_lang}}</a></div>
                                    <div class="span6 closed"><a href="{{ logout_link }}">{{ "Logout" |get_lang }}<img src="{{ _p.web_css }}nuevo_vlearning/img/userlogin/icon_closed.png"/> </a></div>
                                </div>
                            </div>
                        </div>
                {% endif %}
                </div>
            </div>
            <div class="offset3">
            <div class="logo-icpna" style="text-align: right;"><img src="{{ _p.web_css }}nuevo_vlearning/img/logo-icpna.png"> </div>
            <div class="page-content {% if lesson_progress_bar %}header-bar" style="display: none;"{% endif %}">
                    <div class="page-show"></div>
                    {% if lesson_progress_bar is defined %}
                    <div class="">
                        <div class = "btn btn-large btn-white" style="float: left" onclick="javascript:history.back(1)">{{ "GoBack" |get_lang }}</div>
                        <div class = "btn btn-large btn-white" style="float: right">
                            <a href="{{_p.web_modules}}">{{ "Vlearning" |get_lang }}</a>
                            /
                            <a href="{{_p.web_course}}{{_c.code}}/?id_session={{_c.session_id}}">{{ _c.title }}</a>
                        </div>
                    </div>
                    <div class="span4 offset6 user-advanced">
                        {{lesson_progress_bar}}
                    </div>
                    {% endif %}
                </div>
            </div>
    {% if lesson_progress_bar %}
        <div class="span12" style="background-color: #EEE;" id="page-wrap-tab">
        <a href="#">
            <div style="text-align: center"><i class="icon-chevron-up"></i></div>
        </a>
        </div>
    {% endif %}
{% endif %}
