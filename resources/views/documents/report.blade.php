<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta content="IE=Edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
        href="//fonts.googleapis.com/css?family=Google+Sans:400,500|Roboto:400,400italic,500,500italic,700,700italic|Roboto+Mono:400,500,700|Material+Icons">
    <link rel="stylesheet"
        href="https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb/firebase/css/app.css">
    <noscript>
        <link rel="stylesheet"
            href="https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb/firebase/css/ce_bundle.css">
    </noscript>
    <link rel="shortcut icon" href="https://bi.acs.vn/assets/Analytics.ico">
    <title>Tài liệu hướng dẫn sử dụng hệ thống &nbsp;|&nbsp; ACS Analytics Flatform</title>
    <style>
        .video-wrapper {
            margin: 0 0 5px 5px;
        }

        h2,
        h3 {
            margin: 0px 0 0px;
        }

        p,
        li {
            margin: 5px 0;
        }
    </style>
</head>

<body type="article" theme="firebase-theme" class="" layout="docs" pending>
    <devsite-progress type="indeterminate" id="app-progress"></devsite-progress>
    <section class="devsite-wrapper">
        <devsite-header>
            <div class="devsite-header--inner">
                <div class="devsite-top-logo-row-wrapper-wrapper">
                    <div class="devsite-top-logo-row-wrapper">
                        <div class="devsite-top-logo-row">
                            <button type="button" id="devsite-hamburger-menu"
                                class="devsite-header-icon-button button-flat material-icons gc-analytics-event"
                                data-category="Site-Wide Custom Events" data-label="Navigation menu button"
                                visually-hidden aria-label="Open menu">
                            </button>
                            <div class="devsite-product-name-wrapper">
                                <a href="/#"
                                    class="devsite-site-logo-link gc-analytics-event"
                                    data-category="Site-Wide Custom Events" data-label="Site logo"
                                    track-type="globalNav" track-name="firebase" track-metadata-position="nav"
                                    track-metadata-eventDetail="nav">
                                    <img src="https://gm1.ggpht.com/eZ-aOxpH5SYtNA4bqTUCCO6hwXFNJttc41gvtJ_0it6L2N8qOWL7YJJ3UODvigx_5g1MLv5JVYxYHI1j7xoGELqjkUP7pH0TSgr_Tbed01sfa9-xjfVZCk1W0_nTGef3RkJx548q8z8H7JVfn0mtjLfzJz44v8FXG7T6KNQgD1egYJTKzweGBgxNHNyedkcHyhR7VVf5bCVd1UZZOVaKsCzReRMxRsSI27--a3SlRI2kq9JhZOTwRrawQY3N9HF2_n02xaThIXjJmmSOc3HhOwRgCDWkEYYPx_qoQ2CZLvshYi82RstXSAGfGfYFr2htU0nFE0FkphwM-RTPJFjluYNauD68zLI4kvqqx5KeTTCOR3JnVuZmdcsK96wCj-QXdATebP48rSKUDkWrFcrnZcs-MUBlMt6RxOKQukwQU-DRZEk_mSiGdlj0bz-O4LTS8mtCGSJnIAHnGD1FfeS1D0jFAZ2IXXy72ozmXNdIhVYBtyBNsmBUJLYjymSivLShUaPpdRtFgXnBmdHOBw2n4f4RSWziNEgFJbHiYu2JRmw57ynCgvsPHdvRlWv0YW08cmN9izNCuWuvdN-RGoWmMvq_XR7aiIiyMdXsBGFcL6s65VicBRE9fBS4_pu_Sy36xTUKwJxhj39IKjtUZ3DCAxjekLNKTK3scy4NOmNDxHYM2fORVqc6NMpeZ03qodwFY8IfCBG0nbAHNw7AQRBDk2UpwiqAgQ2saIERugdNUhBf93ln3r9LH7Jijnp0=s0-l75-ft-l75-ft"
                                        class="devsite-site-logo" alt="Firebase">
                                </a>

                                <span class="devsite-product-name">
                                    <ul class="devsite-breadcrumb-list">
                                        <li class="devsite-breadcrumb-item">
                                        </li>
                                    </ul>
                                </span>
                            </div>
                            <div class="devsite-top-logo-row-middle">
                                <div class="devsite-header-upper-tabs">
                                    <devsite-tabs class="upper-tabs">
                                        <div class="devsite-tabs-wrapper">
                                            <tab>
                                                <a href="#" class="gc-analytics-event"
                                                    data-category="Site-Wide Custom Events" data-label="Tab: Products">
                                                    Về ACS Analytics
                                                </a>

                                            </tab>
                                            <tab>
                                                <a href="#" class="gc-analytics-event"
                                                    data-category="Site-Wide Custom Events" data-label="Tab: Use Cases">
                                                    Trợ giúp
                                                </a>

                                            </tab>
                                            <tab>
                                                <a href="#" class="gc-analytics-event"
                                                    data-category="Site-Wide Custom Events" data-label="Tab: Pricing">
                                                    Phản hồi
                                                </a>

                                            </tab>
                                            <tab active>
                                                <a href="#" class="gc-analytics-event" aria-label="Docs, selected"
                                                    data-category="Site-Wide Custom Events" data-label="Tab: Docs">
                                                    Tài liệu
                                                </a>

                                            </tab>
                                            <tab>
                                                <a href="#" class="gc-analytics-event"
                                                    data-category="Site-Wide Custom Events" data-label="Tab: Support">
                                                    Thông báo
                                                </a>

                                            </tab>
                                        </div>
                                    </devsite-tabs>
                                </div>
                                <devsite-search enable-signin enable-search enable-suggestions enable-query-completion
                                    project-name="Firebase" tenant-name="Firebase">
                                    <form class="devsite-search-form" action="#" method="GET">
                                        <div class="devsite-search-container">
                                            <div class="devsite-searchbox">
                                                <input placeholder="Tìm kiếm" type="text"
                                                    class="devsite-search-field devsite-search-query" name="q" value=""
                                                    autocomplete="off" aria-label="Search box">
                                                <div class="devsite-search-image material-icons" aria-hidden="true">
                                                </div>
                                            </div>
                                            <button type="button" search-open
                                                class="devsite-search-button devsite-header-icon-button button-flat material-icons"
                                                aria-label="Open search box"></button>
                                        </div>
                                    </form>
                                    <button type="button" search-close
                                        class="devsite-search-button devsite-header-icon-button button-flat material-icons"
                                        aria-label="Close search box"></button>
                                </devsite-search>

                            </div>

                            {{-- Ngôn ngữ --}}
                            <devsite-language-selector>
                                <devsite-select class="devsite-language-selector-menu">
                                    <select class="devsite-language-selector-select" name="language" track-name="click"
                                        track-type="languageSelector">
                                        <option value="id" track-metadata-original-language="en"
                                            track-metadata-selected-language="id" track-name="changed"
                                            track-type="languageSelector">
                                            Bahasa Indonesia
                                        </option>
                                        <option value="en" track-metadata-original-language="en"
                                            track-metadata-selected-language="en" track-name="changed"
                                            track-type="languageSelector" selected="selected">
                                            English
                                        </option>
                                        <option value="es_419" track-metadata-original-language="en"
                                            track-metadata-selected-language="es_419" track-name="changed"
                                            track-type="languageSelector">
                                            Español – América Latina
                                        </option>
                                        <option value="pt_br" track-metadata-original-language="en"
                                            track-metadata-selected-language="pt_br" track-name="changed"
                                            track-type="languageSelector">
                                            Português – Brasil
                                        </option>
                                        <option value="zh_cn" track-metadata-original-language="en"
                                            track-metadata-selected-language="zh_cn" track-name="changed"
                                            track-type="languageSelector">
                                            中文 – 简体
                                        </option>
                                        <option value="ja" track-metadata-original-language="en"
                                            track-metadata-selected-language="ja" track-name="changed"
                                            track-type="languageSelector">
                                            日本語
                                        </option>
                                        <option value="ko" track-metadata-original-language="en"
                                            track-metadata-selected-language="ko" track-name="changed"
                                            track-type="languageSelector">
                                            한국어
                                        </option>
                                    </select>
                                </devsite-select>
                            </devsite-language-selector>

                            <a class="devsite-header-link devsite-top-button button gc-analytics-event"
                                href="https://bi.acs.vn/#/dashboard" data-category="Site-Wide Custom Events"
                                data-label="Site header link">
                                Xem báo cáo
                            </a>

                            <devsite-user signed-in></devsite-user>
                        </div>
                    </div>
                </div>
                <div class="devsite-collapsible-section  ">
                    <div class="devsite-header-background">
                        <div class="devsite-product-id-row">
                            <div class="devsite-product-description-row">
                                <ul class="devsite-breadcrumb-list">
                                    <li class="devsite-breadcrumb-item  ">
                                        <a href="/#"
                                            class="devsite-breadcrumb-link gc-analytics-event"
                                            data-category="Site-Wide Custom Events" data-label="Lower Header"
                                            data-value="1">
                                            Tài liệu hướng dẫn sử dụng
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="devsite-doc-set-nav-row">
                            <devsite-tabs class="lower-tabs">
                                <div class="devsite-tabs-wrapper">
                                    <tab>
                                        <a href="" class="gc-analytics-event" data-category="Site-Wide Custom Events"
                                            data-label="Tab: Overview">
                                            Báo cáo
                                        </a>

                                    </tab>
                                    <tab active>
                                        <a href="" class="gc-analytics-event" aria-label="Guides, selected"
                                            data-category="Site-Wide Custom Events" data-label="Tab: Guides">
                                            Tài liệu
                                        </a>

                                    </tab>
                                </div>
                            </devsite-tabs>

                            <devsite-feedback project-name="Firebase" product-id="719752" bucket="" context=""
                                version="devsite-webserver-20200102-r01-rc03.423746209109273667"
                                data-label="Send Feedback Button" track-type="feedback" track-name="sendFeedbackLink"
                                track-metadata-position="header" project-feedback-url="" project-icon=""
                                project-support-url="" project-support-icon="">
                                <button>Send feedback</button>
                            </devsite-feedback>

                        </div>
                    </div>
                </div>
            </div>
        </devsite-header>
        <devsite-book-nav scrollbars>

            <nav class="devsite-book-nav devsite-nav nocontent">
                <div class="devsite-mobile-header">
                    <button type="button" id="devsite-close-nav"
                        class="devsite-header-icon-button button-flat material-icons gc-analytics-event"
                        data-category="Site-Wide Custom Events" data-label="Close navigation"
                        aria-label="Close navigation">
                    </button>
                    <div class="devsite-product-name-wrapper">
                        <a href="/#" class="devsite-site-logo-link gc-analytics-event"
                            data-category="Site-Wide Custom Events" data-label="Site logo" track-type="globalNav"
                            track-name="firebase" track-metadata-position="nav" track-metadata-eventDetail="nav">
                            <img src="https://gm1.ggpht.com/CDzG_Ar-WwyJFJkaPBawS41DUSzmO7N9GLMxA39Cycvr7jFKlorkB6vhxVYuks4ZbEE6-OwutMvHbTdyCO_wajiUtgUVt1bXqrTqCML7k9Ft0dGq7Uam_ZGTfz9K6-GIhDSjiCSqYmpE_zxpRMPVvYLjBLn2tE1k5kYg_Bj543uD2NfcS4IyT955vEyHgDw9qQEh3NTH5H-1Xt45BTpQUTqr-gnSwmNpwKbJib_XFbd6lgIXepPOdHvqjZi02m3LJyJmM-oBrZCb_q2gLuMo1yKUc-4W50Mx3FQklY9c2VoiAaKJAIakay3hnbaYkOoj5-crv8gO4BquIL2WN6BAJRtzS54wMwdwhaCRMATLPZx2X76S9RnlYk7XMJETCB9E9jooudMCEkzR1q_GuUG7JNRlEXBrW-WloDV_GbKUE1ssVJ1FDEHUwwVUz_RvLg1ULdny-xbEYxpmEXiPZgQDccuz4qKdV_-4gt3PvuIg6FcuGLrQWc7saEt8yXqu8CrvrirzZO5bc98OHBeEVx6uXbSNybvLxSrClIaJu2v3AgEzUSgza030Ro6ROHsUxujS5HRQusVhxYyH5Pgs4O0fspIbgxE-B_cTTlo6_ebK7zPM60Tu_K0iE9Hm6DMnOA5WYeYc2233X5e48z2IWBQiX2U6h8oq45kb1EiNyrlM5TDLrIJ_303dLef2zvgAVAOSnvUGp84nhuF-Pl17RfremMMpZdbQ_2lNn535nwR9i8qzMSfZSq18z9uo_AFq=s0-l75-ft-l75-ft"
                                class="devsite-site-logo" alt="Firebase">
                        </a>
                        <span class="devsite-product-name">
                            <ul class="devsite-breadcrumb-list">
                                <li class="devsite-breadcrumb-item      ">
                                </li>
                            </ul>
                        </span>

                    </div>
                </div>

                <div class="devsite-book-nav-wrapper">
                    <div class="devsite-mobile-nav-top">
                        <ul class="devsite-nav-list">
                            <li class="devsite-nav-item">
                                <a href="/#" class="devsite-nav-title gc-analytics-event
              devsite-nav-has-children" data-category="Site-Wide Custom Events" data-label="Responsive Tab: Products">
                                    <span class="devsite-nav-text" tooltip>
                                        Products
                                    </span>
                                    <span class="devsite-nav-icon material-icons" data-icon="forward">
                                    </span>
                                </a>

                            </li>
                            <li class="devsite-nav-item">
                                <a href="/#" class="devsite-nav-title gc-analytics-event
              devsite-nav-has-children              " data-category="Site-Wide Custom Events"
                                    data-label="Responsive Tab: Use Cases">
                                    <span class="devsite-nav-text" tooltip>
                                        Use Cases
                                    </span>
                                    <span class="devsite-nav-icon material-icons" data-icon="forward">
                                    </span>
                                </a>

                            </li>
                            <li class="devsite-nav-item">
                                <a href="/#" class="devsite-nav-title gc-analytics-event  "
                                    data-category="Site-Wide Custom Events" data-label="Responsive Tab: Pricing">
                                    <span class="devsite-nav-text" tooltip>
                                        Pricing
                                    </span>
                                </a>

                            </li>
                            <li class="devsite-nav-item">
                                <a href="/#" class="devsite-nav-title gc-analytics-event
                            devsite-nav-active" data-category="Site-Wide Custom Events"
                                    data-label="Responsive Tab: Docs">
                                    <span class="devsite-nav-text" tooltip>
                                        Docs
                                    </span>
                                </a>

                                <ul class="devsite-nav-responsive-tabs">
                                    <li class="devsite-nav-item">
                                        <a href="/#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Overview">
                                            <span class="devsite-nav-text" tooltip>
                                                Overview
                                            </span>
                                        </a>
                                    </li>

                                    <li class="devsite-nav-item">
                                        <a href="/#" class="devsite-nav-title gc-analytics-event
              devsite-nav-has-children              devsite-nav-active" data-category="Site-Wide Custom Events"
                                            data-label="Responsive Tab: Guides">
                                            <span class="devsite-nav-text" tooltip menu="_book">
                                                Tài liệu
                                            </span>
                                            <span class="devsite-nav-icon material-icons" data-icon="forward"
                                                menu="_book">
                                            </span>
                                        </a>
                                    </li>

                                    <li class="devsite-nav-item">
                                        <a href="#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Reference">
                                            <span class="devsite-nav-text" tooltip>
                                                Reference
                                            </span>
                                        </a>
                                    </li>

                                    <li class="devsite-nav-item">
                                        <a href="#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Samples">
                                            <span class="devsite-nav-text" tooltip>
                                                Samples
                                            </span>
                                        </a>
                                    </li>

                                    <li class="devsite-nav-item">
                                        <a href="#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Libraries">
                                            <span class="devsite-nav-text" tooltip>
                                                Libraries
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            <li class="devsite-nav-item">
                                <a href="#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Support">
                                    <span class="devsite-nav-text" tooltip>
                                        Support
                                    </span>
                                </a>

                            </li>
                            <li class="devsite-nav-item">
                                <a href="#" class="devsite-nav-title gc-analytics-event
                            " data-category="Site-Wide Custom Events" data-label="Responsive Tab: Go to console">
                                    <span class="devsite-nav-text" tooltip>
                                        Go to console
                                    </span>
                                </a>
                            </li>


                        </ul>
                    </div>
                    <div class="devsite-mobile-nav-bottom">
                        <ul class="devsite-nav-list" menu="_book">
                            <li class="devsite-nav-item"><a href="#" class="devsite-nav-title"><span
                                        class="devsite-nav-text" tooltip>Tài liệu</span></a></li>
                            <li
                                class="devsite-nav-item           devsite-nav-expandable           devsite-nav-accordion">
                                <devsite-expandable-nav collapsed>
                                    <a class="devsite-nav-toggle" aria-hidden="true"></a>
                                    <div class="devsite-nav-title devsite-nav-title-no-path" tabindex="0" role="button">
                                        <span class="devsite-nav-text" tooltip>Tài liệu HDSD</span>
                                    </div>
                                    <ul class="devsite-nav-section">
                                        <li class="devsite-nav-item"><a href="#" class="devsite-nav-title"><span
                                                    class="devsite-nav-text" tooltip>HDSD
                                                    báo cáo</span></a></li>
                                        <li class="devsite-nav-item"><a href="#" class="devsite-nav-title"><span
                                                    class="devsite-nav-text" tooltip>HD Quản
                                                    trị</span></a></li>
                                    </ul>
                                </devsite-expandable-nav>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </devsite-book-nav>
        <section id="gc-wrapper">
            <main role="main" class="devsite-main-content" has-book-nav has-toc>
                <devsite-toc class="devsite-nav"></devsite-toc>
                <devsite-content>


                    <article class="devsite-article">
                        <article class="devsite-article-inner">
                            <style>
                                /* Styles inlined from /styles/docs.css */
                                .center {
                                    text-align: center
                                }

                                /* Used in AdMob code examples */
                                .oldcode {
                                    opacity: .40;

                                    /* IE 8 */
                                    -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=40)";
                                    filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=40);

                                    /* IE <= 7 */
                                    filter: alpha(opacity=40);
                                }

                                .newcode {
                                    font-weight: bold;
                                }

                                /* Device Screenshots */
                                .deviceshot {
                                    max-width: 700px;
                                    max-height: 400px;
                                }


                                /* Start Changelog styles */
                                .badge {
                                    position: relative;
                                    top: -3px;
                                    border-radius: 16px;
                                    display: inline-block;
                                    width: 90px;
                                    height: 32px;
                                    line-height: 32px;
                                    margin-right: 10px;
                                    text-transform: uppercase;
                                    font-size: 13px;
                                    font-weight: 700;
                                    text-align: center;
                                }

                                /* Colours for each type of changelog entry. */
                                /* TODO: Change the colours to match the site colour scheme */

                                .badge-changed {
                                    background-color: #F4B400;
                                    color: #fff;
                                }

                                .badge-fixed {
                                    background-color: #4285F4;
                                    color: #fff;
                                }

                                .badge-important {
                                    background-color: #DB4437;
                                    color: #fff;
                                }

                                .badge-feature {
                                    background-color: #0F9D58;
                                    color: #fff;
                                }

                                .badge-deprecated {
                                    background-color: #DB4437;
                                    color: #fff;
                                }

                                .badge-issue {
                                    background-color: #DB4437;
                                    color: #fff;
                                }

                                .changelog>ul {
                                    padding-left: 0;
                                }

                                .changelog>ul>li {
                                    list-style-type: none;
                                    margin-bottom: 22px;
                                }

                                .firebase-support-release-page .changelog>ul {
                                    list-style-type: none;
                                    padding-left: 110px;
                                }

                                .release-changed::before,
                                .release-feature::before,
                                .release-fixed::before,
                                .release-issue::before,
                                .release-deprecated::before,
                                .release-removed::before,
                                .release-unchanged::before,
                                .release-android::before,
                                .release-android-bom::before,
                                .release-ios::before,
                                .release-admin::before,
                                .release-cpp::before,
                                .release-unity::before,
                                .release-functions::before,
                                .release-javascript::before {
                                    display: block;
                                    float: left;
                                    color: white;
                                    width: 100px;
                                    height: 32px;
                                    margin-left: -110px;
                                    margin-right: 10px;
                                    margin-top: -5px;
                                    padding-top: 4px;
                                    text-transform: uppercase;
                                    font-size: 13px;
                                    font-weight: 700;
                                    text-align: center;
                                    border-radius: 16px;
                                }

                                .release-changed::before {
                                    content: "changed";
                                    background-color: #F4B400;
                                }

                                .release-feature::before {
                                    content: "feature";
                                    background-color: #0F9D58;
                                }

                                .release-fixed::before {
                                    content: "fixed";
                                    background-color: #4285F4;
                                }

                                .release-issue::before {
                                    content: "issue";
                                    background-color: #DB4437;
                                }

                                .release-deprecated::before {
                                    content: "deprecated";
                                    background-color: #E65100;
                                }

                                .release-removed::before {
                                    content: "removed";
                                    background-color: #546E7A;
                                }

                                .release-unchanged::before {
                                    content: "no change";
                                    background-color: #CCCCCC;
                                }

                                .release-android::before {
                                    content: "android";
                                    background-color: #A4C639;
                                }

                                .release-android-bom::before {
                                    content: "android bom";
                                    background-color: #A4C639;
                                }

                                .release-ios::before {
                                    text-transform: none;
                                    content: "iOS";
                                    background-color: #007aff;
                                }

                                .release-admin::before {
                                    content: "admin";
                                    background-color: #039BE5;
                                }

                                .release-cpp::before {
                                    content: "c++";
                                    background-color: #FF8A65;
                                }

                                .release-unity::before {
                                    content: "unity";
                                    background-color: #00cccc;
                                }

                                .release-functions::before {
                                    content: "functions";
                                    background-color: #2C384A;
                                }

                                .release-javascript::before {
                                    content: "javascript";
                                    background-color: #7e57c2;
                                }

                                /* End Changelog styles */


                                /* Docs Overview page styles */

                                .docs-android,
                                .docs-ios,
                                .docs-web,
                                .docs-cpp,
                                .docs-unity {
                                    height: 64px;
                                    width: 64px;
                                    margin: 10px 16px 0 -16px;
                                }

                                .firebase-docs-overview .devsite-landing-row h2 {
                                    margin: 80px 0 -40px;
                                }

                                .devsite-landing-row-3-up .docs-landing-row-item {
                                    width: calc((100% - 80px)/2);
                                    display: inline-block;
                                }

                                .docs-landing-icon {
                                    font-size: 48px;
                                    height: 48px;
                                    margin: 11px;
                                    width: 48px;
                                }

                                .firebase-reference-list {
                                    display: -ms-flex;
                                    display: flex;
                                    -ms-flex-wrap: wrap;
                                    flex-wrap: wrap;
                                    list-style: none;
                                    margin: 0 0 40px;
                                    padding: 0;
                                }

                                .firebase-reference-list-item {
                                    -ms-flex: 0 0 50%;
                                    flex: 0 0 50%;
                                    margin: 40px 0 0;
                                    padding: 0;
                                }

                                .firebase-reference-list-item>a {
                                    -ms-flex-align: center;
                                    align-items: center;
                                    display: -ms-flex;
                                    display: flex;
                                }

                                .firebase-reference-list-item>a:not(:hover) h3 {
                                    color: #424242;
                                }

                                .firebase-reference-list-item>a:not(:hover) li,
                                .firebase-reference-list-item>a:not(:hover) p {
                                    color: #757575;
                                }

                                .firebase-reference-list-item .docs-landing-icon {
                                    -ms-flex-align: center;
                                    align-items: center;
                                    background-color: #f5f5f5;
                                    border-radius: 50%;
                                    -ms-flex: 0 0 88px;
                                    flex: 0 0 88px;
                                    height: 88px;
                                    -ms-justify-content: center;
                                    justify-content: center;
                                    margin: 0 16px 0 0;
                                    padding: 20px;
                                }

                                .firebase-reference-list-item h3 {
                                    -ms-flex: 0 0 auto;
                                    flex: 0 0 auto;
                                    font-size: 18px;
                                    line-height: 28px;
                                    margin: 0;
                                }

                                .firebase-reference-list-item ul {
                                    list-style: none;
                                    padding: 0;
                                }

                                .docs-android,
                                .docs-ios,
                                .docs-web,
                                .docs-cpp,
                                .docs-unity {
                                    height: 88px;
                                    width: auto;
                                }

                                .firebase-reference-list-item.docs-android .docs-landing-icon {
                                    color: #00bfa5;
                                }

                                .firebase-reference-list-item.docs-cpp .docs-landing-icon {
                                    color: #fd8c09;
                                }

                                .firebase-reference-list-item.docs-ios .docs-landing-icon {
                                    color: #16aaca;
                                }

                                .firebase-reference-list-item.docs-unity .docs-landing-icon {
                                    color: #000000;
                                }

                                .firebase-reference-list-item.docs-web .docs-landing-icon {
                                    color: #c52062;
                                }

                                .firebase-reference-list-item.docs-http .docs-landing-icon {
                                    color: #3F51B5;
                                }

                                @media (max-width: 1000px) {
                                    .firebase-reference-list {
                                        -ms-flex-direction: column;
                                        flex-direction: column;
                                    }
                                }

                                /* end Docs Overview page styles */

                                /* Full-page platform selector for Crashlytics docs */
                                /* Note: h3 in selector content must include hide-from-toc */

                                .full-page-selector,
                                .full-page-selector-content {
                                    border: none;
                                    font-size: inherit;
                                }

                                .full-page-selector>.kd-buttonbar {
                                    margin: 32px 0px;
                                }

                                .full-page-selector>section {
                                    display: none;
                                }

                                .full-page-selector-content>.selected {
                                    padding: 0;
                                    font-size: inherit;
                                }

                                .full-page-selector-content>.kd-buttonbar {
                                    display: none;
                                }

                                .select-page {
                                    display: none;
                                }

                                .select-page+.ds-selector-tabs,
                                .after-selection~.ds-selector-tabs {
                                    border: none;
                                    font-size: inherit;
                                }

                                .select-page+.ds-selector-tabs>.kd-buttonbar {
                                    margin: 32px 0px;
                                }

                                .select-page+.ds-selector-tabs>section {
                                    display: none;
                                }

                                .after-selection~.ds-selector-tabs>.selected {
                                    padding: 0;
                                    font-size: inherit;
                                }

                                .after-selection~.ds-selector-tabs>section p {
                                    line-height: 24px;
                                }

                                .after-selection~.ds-selector-tabs>.kd-buttonbar {
                                    display: none;
                                }

                                /* End full-page platform selector */

                                /* Custom beta and alpha indicators (v1 format, followed by v2 format) */

                                .devsite-nav-alpha .devsite-nav-icon::before,
                                .devsite-nav-icon[data-icon="alpha"]::before {
                                    content: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAxOCAxOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTMuMjA2IDE1YTU0LjIxNCA1NC4yMTQgMCAwIDEtLjkyLTIuNTc4bC0uMTQtLjQxNGE4MS43NiA4MS43NiAwIDAgMC0uMTM2LS40MDJDMTAuODUgMTMuNjcyIDkuNTYyIDE0LjUgNy41IDE0LjUgNC44MSAxNC41IDMgMTIuNjE2IDMgOXMxLjgxLTUuNSA0LjUtNS41YzIuMDYyIDAgMy4zNS44MjggNC41MSAyLjg5NGwuMTM3LS40MDIuMTM5LS40MTRjLjM4LTEuMTI3LjY1My0xLjkwMi45Mi0yLjU3OGgyLjE2NmMtLjM2NC44My0uNjcyIDEuNjgtMS4xOSAzLjIxN2wtLjE0LjQxM0E1Ny45OSA1Ny45OSAwIDAgMSAxMy4xOTQgOWMuMjc1LjcxLjU1NCAxLjQ5OC44NDggMi4zN2wuMTQuNDEzYy41MTggMS41MzguODI2IDIuMzg3IDEuMTkgMy4yMTdoLTIuMTY2ek03LjUgNS41QzUuOTMyIDUuNSA1IDYuNDcgNSA5YzAgMi41My45MzIgMy41IDIuNSAzLjUgMS40OTcgMCAyLjQ0NC0uOTE3IDMuNTM1LTMuNUM5Ljk0NCA2LjQxNyA4Ljk5NyA1LjUgNy41IDUuNXoiIGZpbGw9InJnYmEoMCwwLDAsLjM4KSIgZmlsbC1ydWxlPSJub256ZXJvIi8+PC9zdmc+');
                                }

                                .devsite-nav-beta .devsite-nav-icon::before,
                                .devsite-nav-icon[data-icon="beta"]::before {
                                    content: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAxOCAxOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTIuMjM2IDcuNjgzQTQgNCAwIDAgMSAxMCAxNUg3djJINVY0YTIgMiAwIDAgMSAyLTJoMi41YTMuNSAzLjUgMCAwIDEgMi43MzYgNS42ODN6TTcgMTNoM2EyIDIgMCAxIDAgMC00SDd2NHptMC02aDIuNWExLjUgMS41IDAgMCAwIDAtM0g3djN6IiBmaWxsPSJyZ2JhKDAsMCwwLC4zOCkiIGZpbGwtcnVsZT0ibm9uemVybyIvPjwvc3ZnPg==');
                                }


                                .firebase-platform-label {
                                    font: 500 12px/24px Roboto, sans-serif;
                                }

                                /* Full-page platform selector dropdown */
                                /* Note: use menu variables in _elements.html file */

                                .full-page-selector-dropdown {
                                    border: none;
                                    position: relative;
                                    float: left;
                                    display: inline-block;
                                }

                                .full-page-selector-dropdown>.ds-selector-tabs {
                                    display: none;
                                    position: absolute;
                                    min-width: 160px;
                                    z-index: 1;
                                }

                                .full-page-selector-dropdown:hover .ds-selector-tabs {
                                    display: block;
                                    margin: 0;
                                }

                                .full-page-selector-dropdown:hover .kd-tabbutton {
                                    display: block;
                                    border: none;
                                    height: inherit;
                                    text-align: left;
                                }

                                .full-page-selector-dropdown:hover .kd-buttonbar {
                                    border: none;
                                    height: inherit;
                                }

                                .full-page-selector-dropdown h3:hover {
                                    color: white;
                                    background-color: #039be5;
                                }

                                .full-page-selector-dropdown:hover .devsite-overflow-menu-button {
                                    display: none;
                                }
                            </style>
                            <div class="devsite-article-meta">
                                <ul class="devsite-breadcrumb-list">
                                    <li class="devsite-breadcrumb-item
             ">
                                        <a href="https://firebase.google.com/?authuser=0"
                                            class="devsite-breadcrumb-link gc-analytics-event"
                                            data-category="Site-Wide Custom Events" data-label="Breadcrumbs"
                                            data-value="1">
                                            ACS Analytics Flatform

                                        </a>
                                    </li>
                                    <li class="devsite-breadcrumb-item
             ">
                                        <div class="devsite-breadcrumb-guillemet material-icons" aria-hidden="true">
                                        </div>
                                        <a href="https://firebase.google.com/docs?authuser=0"
                                            class="devsite-breadcrumb-link gc-analytics-event"
                                            data-category="Site-Wide Custom Events" data-label="Breadcrumbs"
                                            data-value="2">
                                            Tài liệu

                                        </a>
                                    </li>
                                    <li class="devsite-breadcrumb-item
             ">
                                        <div class="devsite-breadcrumb-guillemet material-icons" aria-hidden="true">
                                        </div>
                                        <a href="https://firebase.google.com/docs/guides?authuser=0"
                                            class="devsite-breadcrumb-link gc-analytics-event"
                                            data-category="Site-Wide Custom Events" data-label="Breadcrumbs"
                                            data-value="3">
                                            Tài liệu hướng dẫn sử dụng báo cáo

                                        </a>
                                    </li>
                                </ul>
                                <devsite-page-rating position="header" selected-rating="0" hover-rating-star="0">
                                </devsite-page-rating>
                            </div>

                            <h1 class="devsite-page-title">Hướng dẫn sử dụng báo cáo </h1>

                            <devsite-toc class="devsite-nav" devsite-toc-embedded>
                            </devsite-toc>

                            <div class="devsite-article-body clearfix   ">
                                <div class="video-wrapper">
                                    <img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.2&permmsgid=msg-a:r-1824171115586600699&th=16fa771408ad21b2&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8rUysbJW0pkbKndFeQsL4Myp4HPd_u9vlQdDx4iPxTgw_qXRjYWdLImewYAaHWbTSREFP_wzWDAqflsdr9Ne-V3vVNIzK2Hxda5h1c2vmUhY2btigfn1woA6Q&disp=emb&realattid=ii_k5esv5o61">
                                </div>
                                <h2 id="prerequisites">Đăng nhập</h2>
                                <ul>
                                    <li>
                                        <p>Sau khi truy cập, giao diện hiển thị cửa sổ đăng nhập như sau:</p>
                                    </li>
                                    <li>
                                        <p>Tên đăng nhập và mật khẩu vào hệ thống là tài khoản được cấp.
                                            Nhập tên đăng nhập, mật khẩu và ngôn ngữ sử dụng chọn nút [Login] để vào hệ
                                            thống. </p>
                                    </li>
                                </ul>
                                <hr>
                                <h2 id="prerequisites">Dashboard</h2>
                                <ul>
                                    <li>
                                        <p>Từ giao diện chính chọn Dashboard trên thanh Menu trái.</p>
                                    </li>
                                    <li>
                                        <p>Trong giao diện này sẽ hiển thị 2 nội dung chính như phần đánh dấu:</p>
                                        <ul>
                                            <li>Dữ liệu tổng trên các box(1)</li>
                                            <li>Biểu đồ phân tích tổng hợp( 2)</li>
                                        </ul>
                                    </li>
                                </ul>
                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.4&permmsgid=msg-a:r-7413385125596206442&th=16fa773402a3df24&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ876cJTxGGr4LnQxqSp-v8RYTtrLTyiZ-UdtqgSu56yRmg5XC6q9rmlW1mp-yc4qhhdFeo06STPWgkmhYsVtbZkgTZZR5notNbGE738Hnd0_W9M61hSNmSIRmM&disp=emb&realattid=ii_k5eswvmg3"
                                        alt="">
                                </div>
                                <ol>
                                    <li>
                                        <p>Dữ liệu tổng</p>

                                        <ul>
                                            <li> Bao gồm 4 box cho phép người dùng xem nhanh dữ liệu của 1 chỉ số cụ thể
                                                cho 1 tổ chức hay địa điểm trong khoảng thời gian(theo ngày, giờ).</li>
                                            <li> Vui lòng chọn nút bên phải trên cùng của box để thay đổi thông tin.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <p>Biểu đồ phân tích</p>

                                        <ul>
                                            <li>
                                                <p>Hiển thị biểu đồ với các chỉ số, địa điểm và thời gian. Cho phép xem
                                                    theo giờ, ngày, tuần tháng, năm tùy thuộc vào mốc thời gian và chỉ
                                                    số bạn muốn xem. </p>
                                            </li>
                                            <li>
                                                <p>Kích nút để thay đổi thông tin thời gian và các chỉ số
                                                </p>
                                            </li>
                                            <li>
                                                <p>Các chỉ số thể hiện giá trị phần trăm dữ liệu thể hiện bằng đường với
                                                    trục bên phải của biểu đồ. Kích chọn checkbox cạnh chỉ số để thay
                                                    đổi loại biểu đồ. Mỗi lần thay đổi hệ thống sẽ lưu thông tin đã chọn
                                                    tương ứng với tài khoản bạn đang sử dụng. Điều này sẽ giúp bạn xem
                                                    nhanh với lần truy cập sau mà không cần chọn lại chỉ số.
                                                </p>
                                            </li>
                                        </ul>
                                    </li>
                                </ol>
                                <hr>
                                {{-- Module Footfall --}}
                                <h2 id="console"><strong>Module</strong>: Hệ thống Footfall</h2>
                                <p>Hệ thống đếm số người vào-ra tại khu vực, bao gồm 6 báo cáo chính như hình dưới:</p>
                                <ul>
                                    <li> Báo cáo tổng quan. </li>
                                    <li>Live Accupancy. </li>
                                    <li> Hiệu qủa hoạt động. </li>
                                    <li> Báo cáo so sánh. </li>
                                    <li>Phân tích xu hướng </li>
                                    <li> Biểu đồ nhiệt Heatmap </li>
                                </ul>
                                <h3 id="create-firebase-project"><strong>1</strong>. Báo cáo tổng quan</h3>
                                <p>Cho phép người dùng xem nhanh các chỉ số đếm người, cùng biểu đồ thể hiện top cửa
                                    hàng có lượng khách vào nhiều nhất của một vùng viền hay cả một tổ chức, đơn vị.</p>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/0?ui=2&ik=0b38e73b8d&attid=0.8&permmsgid=msg-f:1655766891418584201&th=16fa773711db8489&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8RI_8mQ8sc5fIGGT3vsgLMlxkVp5I9SNYCsrjYRktH_FXA0HtzmqEd-Fm3XfaFB_Pbx42wTWWpQPwDNGHpSrwiuHBizpMF2zL4-6ZKJNjtBn7K1hbl1FH1xAY&disp=emb&realattid=ii_k5esxaaf7"
                                        alt=""></div>
                                <p>Để thay đổi chỉ số khác hay một địa điểm, kích chuột vào nút . Mỗi lần thay đổi hệ
                                    thống sẽ lưu thông tin đã chọn tương ứng với tài khoản bạn đang sử dụng.</p>
                                <p>Điều này sẽ giúp bạn xem nhanh với lần truy cập sau mà không cần chọn lại chỉ số.</p>

                                <h3 id="create-firebase-project"><strong>2</strong>. Live Accupancy</h3>
                                <p>Thể hiện số người đang mua sắm và tổng số lượt người ra vào mua sắm cho tới thời điểm
                                    hiện tại.</p>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/0?ui=2&ik=0b38e73b8d&attid=0.10&permmsgid=msg-f:1655766891418584201&th=16fa773711db8489&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_zSzux_IEQWZvgOdicxASp325-115ocQwzOt1m-5VrxzZR3hKcFFb96DlZ7L_ZGyBQ8ipWZta0Qi3SD3Yn9xQCi7yOqt2P8Xi-zqfG0rDLCBKJLDf_GDU-KOE&disp=emb&realattid=ii_k5esxjag9"
                                        alt=""></div>
                                <p>Ngoài ra, báo cáo so sánh cùng khung giờ số người vào ra mua sắm với ngày hôm trước
                                    và thể hiện người ra vào theo khung giờ, ngày theo các dạng biểu đồ và bảng như trên
                                    hình. </p>


                                <h3 id="create-firebase-project"><strong>3</strong>. Báo cáo hiệu quả hoạt động</h3>
                                <p>Bao gồm 2 báo cáo: Báo cáo theo cửa hàng và báo cáo theo chỉ số.</p>
                                <ol>
                                    <li>Báo cáo theo cửa hàng
                                        <p>Như hình dưới đây thể hiện báo cáo theo cửa hàng. Đánh giá hiệu quả hoạt động
                                            của cửa hàng trên toàn hệ thống và mối tương quan giữa các chỉ số</p>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.11&permmsgid=msg-a:r-7413385125596206442&th=16fa773402a3df24&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8j6_hpDu3SHPaszwzwFpB_wUvz2u6fkE2Ult-SSem_tN7seQZQK9eb8LSnIYNnRfRwdpE1rtRe0Eq0uLCCjCeWOiG3KsDa8iMnTvspAbL42js2iu13j6571XI&disp=emb&realattid=ii_k5esxou910"
                                                alt=""></div>
                                        <p>Báo cáo gồm 3 phần: (1) Trên thanh menu cho phép bạn cho tổ chức, cửa hàng,
                                            khung thời gian, thời kì, chỉ số và nút [Áp dụng]. Nút áp dụng được thực
                                            hiện sau khi bạn chọn các mục theo ý muốn. (2) Biểu đồ thể hiện các chỉ số
                                            với các chỉ số đã chọn. (3) Bảng thể hiện chi tiết </p>
                                        <p>Bạn thay đổi chỉ số bằng cách chọn vào combox và thay đổi khoảng thời
                                            gian và sau đó nhấn [Áp dụng]. </p>

                                    </li>
                                    <li>Báo cáo theo chỉ số
                                        <p>Báo cáo chi tiết một chỉ số theo khoảng thời gian, thời kì qua các khung giờ,
                                            ngày, tháng, năm cửa một cửa hàng cụ thể. </p>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.14&permmsgid=msg-a:r-7413385125596206442&th=16fa773402a3df24&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_fkhDg2LrofGo9nzvkpNsfr4fHPRT9o0gEFwSLL2ckcdOBvBJ98i6e_FaAhDiRau1oadhn5T94hlDlVP74lYP359y9ACVheywiDC88UQQreYk1fHiauhboa_4&disp=emb&realattid=ii_k5esy27k13"
                                                alt=""></div>
                                    </li>
                                </ol>

                                <h3 id="create-firebase-project"><strong>4</strong>. Báo cáo so sánh</h3>
                                <p>Bao gồm so sánh theo cửa hàng, theo thời gian và chỉ số. Lựa chọn một cửa hàng, thời
                                    gian hay chỉ số làm căn cứ chính để đánh giá hiệu quả hoạt động của của cửa hàng,
                                    thời gian hay chỉ số khác.</p>
                                <p>Tìm kiếm và áp dụng linh hoạt công thức của cửa hàng hiệu quả cho các cửa hàng khác
                                    trên hệ thống.</p>
                                <ol>
                                    <li> Báo cáo so sánh theo cửa hàng
                                        <p>So sánh hiệu quả hoạt động của 2 cửa hàng khác nhau theo từng chỉ số. Lựa
                                            chọn một cửa hàng làm căn cứ chính để đánh giá hiệu quả hoạt động của của
                                            cửa hàng khác. Tìm kiếm và áp dụng linh hoạt công thức của cửa hàng hiệu quả
                                            cho các cửa hàng khác trên hệ thống.</p>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.15&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8TJ09BezhrdhbbibpEdDhyQEg6sw2AawPB1_7lZRdfQV30aB-Fl8IpKB9tlJBYz6xmoFgL51Tfu_xqKNIbaI6dv4O16bdWi7l0KU1NGdE1eocFWOE-eR2R970&disp=emb&realattid=ii_k5eyvr6o14"
                                                alt=""></div>
                                        <p>Như trên hình là so sánh chỉ số khách vào mua sắm cửa 2 vùng miền Bắc và Nam
                                            cửa một đơn vị . Biểu đồ thể hiện mức chênh lệch giữa các ngày, bảng biểu
                                            thể hiện chị tiết sự chênh lệch giữa các vùng miền. </p>
                                    </li>
                                    <li>Báo cáo so sánh theo thời gian
                                        <br>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.16&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_cb7XOr31UWIlPTlIaXuo7_XyfT8lTdQXy9tZe6-pCMeZhRmXdeARy7_9Bjy-qdbwgQZn0DGhehUL_aOpcLgo_PxVlbeYJ0RmhJzlTsNsQOSfOG85RBKLml54&disp=emb&realattid=ii_k5eyvvk515"
                                                alt=""></div>
                                    </li>
                                    <li> Báo cáo so sánh theo chỉ số
                                        <br>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.17&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ99gx_UBvL8a-Neh9DylUy0WwhnqlmsGBphwMULLuWKmP8-lzPySbSu64S7ryiihz4JsJJe3BYyei3rAWfJylWrZ60PTQwWXzwiVNmFzsnRVF3PlmGpU_19ZbM&disp=emb&realattid=ii_k5eyvzr316"
                                                alt=""></div>
                                    </li>
                                </ol>


                                <h3 id="create-firebase-project"><strong>5</strong>. Báo cáo xu hướng</h3>
                                <p>Bao gồm so sánh theo cửa hàng, theo thời gian và chỉ số. Đánh giá xu hướng hoạt động
                                    của các cửa hàng, chỉ số theo từng thời điểm, đánh giá giữa ngày làm việc và ngành
                                    cuối tuần để tìm ra xu hướng mua sắm của khách hàng từ đó xây dựng chiến lược
                                    Marketing và hệ thống hóa nhân sự.</p>
                                <ol>
                                    <li> Báo cáo xu hướng theo cửa hàng
                                        <br>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.15&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8TJ09BezhrdhbbibpEdDhyQEg6sw2AawPB1_7lZRdfQV30aB-Fl8IpKB9tlJBYz6xmoFgL51Tfu_xqKNIbaI6dv4O16bdWi7l0KU1NGdE1eocFWOE-eR2R970&disp=emb&realattid=ii_k5eyvr6o14"
                                                alt=""></div>
                                        <p>Như trên hình là so sánh chỉ số khách vào mua sắm cửa 2 vùng miền Bắc và Nam
                                            cửa một đơn vị . Biểu đồ thể hiện mức chênh lệch giữa các ngày, bảng biểu
                                            thể hiện chị tiết sự chênh lệch giữa các vùng miền. </p>
                                    </li>
                                    <li>Báo cáo xu hướng theo chỉ số
                                        <br>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.21&permmsgid=msg-a:r-6098982378884044988&th=16fa8286403fd75c&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ9flNman-2emorCJGhG9lHTuy68ZhuxYc7uMvRzFGY2jVpgcOALFVwocKs3Qv2Bh8U-OW4s4E5Fsa6XLoywgrkRIStvNXmuxm0GcfFhTLaGWTmejcScmlmToYU&disp=emb&realattid=ii_k5f008nm20"
                                                alt=""></div>
                                    </li>
                                </ol>

                                <h3 id="create-firebase-project"><strong>6</strong>. Biểu đồ nhiệt Heatmap</h3>
                                <p>Bao gồm so sánh theo cửa hàng, theo thời gian và chỉ số. Đánh giá xu hướng hoạt động
                                    của các cửa hàng, chỉ số theo từng thời điểm, đánh giá giữa ngày làm việc và ngành
                                    cuối tuần để tìm ra xu hướng mua sắm của khách hàng từ đó xây dựng chiến lược
                                    Marketing và hệ thống hóa nhân sự.</p>
                                <ul>
                                    <li style="list-style: none;">
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.40&permmsgid=msg-a:r5667873091600723491&th=16fa8ab5eb9df0d4&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ-rP32iz_YyuYaN7kDFVQLMFQ8t-zWhVhME654-2Dvzkpx2cLxbS54ISND9oQiUzTE9q3t4-a_dztu7UrylK2PwL3ivolHj7uWgNdkiE3--jhNE2iQvXwDs0jo&disp=emb&realattid=ii_k5f54ga939"
                                                alt="">
                                        </div>
                                    </li>
                                </ul>

                                <hr>
                                {{-- MOdule Giới tính- Độ Tuổi --}}
                                <h2 id="console"><strong>Module</strong>: Giới tính - Độ tuổi</h2>
                                <p>Phân tích giới tính và độ tuổi cung cấp công cụ tối ưu cho việc thu thập thông tin về
                                    hành vi (Age & Gender) và xu hướng mua sắm của khách hàng bằng công nghệ nhận diện
                                    khuôn mặt. </p>
                                <p>Bằng việc nhận diện khuôn mặt khách hàng khi họ bước vào cửa hàng (Outlets) hay một
                                    khu vực nào đó và kết nối với dữ liệu Shopper Visits, các hãng bán lẻ có một hồ sơ
                                    rõ ràng về tập khách hàng của họ và thực thi các kế hoạch Marketing, màn hình quảng
                                    cáo để thu hút nhiều khách hàng hơn</p>

                                <h3 id="create-firebase-project"><strong>1</strong>.Tổng quan</h3>
                                <p>Dữ liệu realtime được đưa lên báo cáo của một cửa hàng, đơn vị.</p>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.23&permmsgid=msg-a:r-7526792037231733706&th=16fa8575bceba9ab&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_T55d37suZ5BmtTV4mkEznUnnjPl2J8-uRUo9S7766vmN3Mem9dYW-U6DOpOB7fek3j6fDAxtjTFxxq2RzcKEC-o9qJsOgdkU6d-j_RORTjekc-c7TONYAKeE&disp=emb&realattid=ii_k5f1tz8022"
                                        alt=""></div>
                                <p>Thể hiện lượng khách ra vào mua sắm là nam hay nữ, thuộc độ tuổi nào. Unknown (không
                                    xác định): là những người đi qua mà Camera không nhận dạng được. Ví dụ như: đeo khẩu
                                    trang, quay ngang, quay đầu lại sau,..
                                    .</p>

                                <h3 id="create-firebase-project"><strong>2</strong>. Phân tích </h3>
                                <p>Phân tích độ tuổi khách nam hay nữ ra vào trong khoảng thời gian, thời điểm.</p>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.24&permmsgid=msg-a:r-7526792037231733706&th=16fa8575bceba9ab&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ9SztOjW0mI5BsV54CYL4G0eEnAi5QK1Zcu9z-0CkneVpcXqIGy5vf1fdjpC-7fPa9wUaq5DRS11i5tIUwKPlYn598b-Z3hUm5mahvjvZp-TwS5dKv8qeY5U3c&disp=emb&realattid=ii_k5f1u3dr23"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>3</strong>. Độ tuổi</h3>
                                <br>
                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.25&permmsgid=msg-a:r-7526792037231733706&th=16fa8575bceba9ab&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ985MRlvVBaum_ogrF3o5nKhstHjfMficZwYAK9QVX95OQLiFim6jucZusocGgXZX3JvQRfaNhw5roexUX6ody_2fRRTYgjPvmNmP_siXO1ponkIubNePR8WWE&disp=emb&realattid=ii_k5f1u5ue24"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>4</strong>. Giới tính</h3>

                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.26&permmsgid=msg-a:r-7526792037231733706&th=16fa8575bceba9ab&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_FYwH6gOA-8T8eof0Jg7q8JR_U29wblryuuorK4e4J8FrPnDYNRlb7MqsD24rDW-mQvnLmEVA8RU9V8x9Yumg6QQ1rwy1bMg8A4xmRRiiSCNtHDao0NUXDkcw&disp=emb&realattid=ii_k5f1u9a825"
                                        alt=""></div>
                                <hr>

                                {{-- Trải nghiệm khách hàng --}}
                                <h2 id="console"><strong>Module</strong>: Trải nghiệm khách hàng</h2>
                                <p>Đo lường mức độ hài lòng khách hàng, ACS Smileys giúp doanh nghiệp đo lường các chỉ
                                    số đánh giá mức độ trung thành với thương hiệu NPS (Net Promoter Score) và điểm hài
                                    lòng của khách hàng CX index (Customer Experience). Từ đó phân tích dữ liệu và
                                    chuyển từ phản hồi thành những cải tiến liên tục.</p>

                                <h3 id="create-firebase-project"><strong>1</strong>.Tổng quan</h3>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.27&permmsgid=msg-a:r-1685029167245159706&th=16fa867762e039d3&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ-bqdMkfn33OtE1OOmk5lRMV9b8fYAnWF45_c6be1JbcmAZolAkhWzuDjMkvL3K-UkRlJqkUbE8ZEOH804Z_chVSUO5jNad0tbYfZrMMHT2uno7JNSoLe3Ef_E&disp=emb&realattid=ii_k5f2gmwu26"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>2</strong>. Phân tích chỉ số </h3>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.28&permmsgid=msg-a:r-1685029167245159706&th=16fa867762e039d3&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8piXSwgzW3a_lbXpnTGZWzYTkby7--Goe61YbbPTEVa_Um5dxVn-z3PbBFxjH6JbsGVN0DiZawUCsrJq7c8_SZQ_ErH81Wx0SdroY0e9kRI30yNWj2FEYdZx8&disp=emb&realattid=ii_k5f2gpnj27"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>3</strong>. Báo cáo so sánh</h3>
                                <p>Cho phép thêm mới, sửa , xóa, tìm kiếm kho hàng</p>
                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.29&permmsgid=msg-a:r-1685029167245159706&th=16fa867762e039d3&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ-GVg8Dk8OQ7dgW8-6OUIC7pQFPFZfwfm_9io6eASe0eC0MLOObpxpeApP8Hv33w1NeZCc3kBy35xJPx61iWoFyu-2BYW9xyTtUGSgjOj7JxfpZrjv6xweJdXs&disp=emb&realattid=ii_k5f2gtl428"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>4</strong>. Lý do không hài lòng</h3>

                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.30&permmsgid=msg-a:r-1685029167245159706&th=16fa867762e039d3&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_Ytih2-s5yibUs7jE-1gUa14H95_fQUp6jVPbsmurcRrzfKgaM2UxGh82mJW_V893B0nLm1UG-Z8fsE4kQO8wetmI_o-C1Lw1KY9bDHbiL6iM9aPFGju1WGiI&disp=emb&realattid=ii_k5f2gxya29"
                                        alt=""></div>


                                <h3 id="create-firebase-project"><strong>4</strong>. Thông tin khách hàng</h3>

                                <div style="text-align:center">
                                    <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.31&permmsgid=msg-a:r-1685029167245159706&th=16fa867762e039d3&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_l80-hKcxHT0m4lLOxp1Jgf9rdvEObAMrXDtYJ3tA4SRXr100Uhk8jEaeYCZgZkFp95ml7cSkqV2VSHdi2-PdGtSuwpPpPbmFWqG2pMmXRrR6FXiNnXo35Llc&disp=emb&realattid=ii_k5f2h1nf30"
                                        alt=""></div>
                                <hr>
                                {{-- Báo cáo tổng hợp --}}
                                <h2 id="console"><strong>Module</strong>: Báo cáo tổng hợp</h2>
                                <h3 id="create-firebase-project"><strong>1</strong>. Mô hình Boston</h3>
                                <p>Mô hình Boston là thước đo tăng trưởng tổng quan, định vị hiệu quả hoạt động của tất
                                    cả các cửa hàng trên cùng một mặt phẳng. Với mô hình này, nhà quản lý có thể đánh
                                    giá hiệu quả hoạt động của các cửa hàng theo từng cặp chỉ số để nắm được vị trí của
                                    cửa hàng so với mức hiệu quả hoạt động trung bình của toàn hệ thống.</p>
                                <div style="text-align:center"><img
                                        src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.32&permmsgid=msg-a:r-5492827268071956762&th=16fa8723e4bebc22&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8ZR5BCkoE6hb02vdMNL6BWE9FqGVPXcsDQXkKeFKa7wvx2jLa-tqJgSu7StAl1zljQJ_c2tiGJf7FQ8EhcIe-gQtuef7C2PnOABNdzmk50MhsozMfYu24blNk&disp=emb&realattid=ii_k5f2shun31"
                                        alt=""></div>

                                <h3 id="create-firebase-project"><strong>2</strong>. Báo cáo hiệu quả hoạt động</h3>
                                <p>Bao gồm 2 báo cáo: Báo cáo theo cửa hàng và báo cáo theo chỉ số.</p>
                                <ol>
                                    <li>Báo cáo theo cửa hàng
                                        <p>Như hình dưới đây thể hiện báo cáo theo cửa hàng. Đánh giá hiệu quả hoạt động
                                            của cửa hàng trên toàn hệ thống và mối tương quan giữa các chỉ số</p>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.33&permmsgid=msg-a:r-5492827268071956762&th=16fa8723e4bebc22&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ9oIuFys33h6Vrhn_B_OYXUCgNBTxtHe9r2SglGQvlJifgqB_wygNg_1Xf0auYmW07JAD26CLUC6HtzOwEAFlBp2tVTDowGgVUbW9NDictTho4x05NqI6LAz0A&disp=emb&realattid=ii_k5f2slrp32"
                                                alt=""></div>
                                        <p>Báo cáo gồm 3 phần: (1) Trên thanh menu cho phép bạn cho tổ chức, cửa hàng,
                                            khung thời gian, thời kì, chỉ số và nút [Áp dụng]. Nút áp dụng được thực
                                            hiện sau khi bạn chọn các mục theo ý muốn. (2) Biểu đồ thể hiện các chỉ số
                                            với các chỉ số đã chọn. (3) Bảng thể hiện chi tiết </p>
                                        <p>Bạn thay đổi chỉ số bằng cách chọn vào combox và thay đổi khoảng thời
                                            gian và sau đó nhấn [Áp dụng]. </p>

                                    </li>
                                    <li>Báo cáo theo chỉ số
                                        <p>Báo cáo chi tiết một chỉ số theo khoảng thời gian, thời kì qua các khung giờ,
                                            ngày, tháng, năm cửa một cửa hàng cụ thể. </p>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.14&permmsgid=msg-a:r-7413385125596206442&th=16fa773402a3df24&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_fkhDg2LrofGo9nzvkpNsfr4fHPRT9o0gEFwSLL2ckcdOBvBJ98i6e_FaAhDiRau1oadhn5T94hlDlVP74lYP359y9ACVheywiDC88UQQreYk1fHiauhboa_4&disp=emb&realattid=ii_k5esy27k13"
                                                alt=""></div>
                                    </li>
                                </ol>

                                <h3 id="create-firebase-project"><strong>4</strong>. Báo cáo so sánh</h3>
                                <p>Bao gồm so sánh theo cửa hàng, theo thời gian và chỉ số. Lựa chọn một cửa hàng, thời
                                    gian hay chỉ số làm căn cứ chính để đánh giá hiệu quả hoạt động của của cửa hàng,
                                    thời gian hay chỉ số khác.</p>
                                <p>Tìm kiếm và áp dụng linh hoạt công thức của cửa hàng hiệu quả cho các cửa hàng khác
                                    trên hệ thống.</p>
                                <ol>
                                    <li> Báo cáo so sánh theo cửa hàng
                                        <p>So sánh hiệu quả hoạt động của 2 cửa hàng khác nhau theo từng chỉ số. Lựa
                                            chọn một cửa hàng làm căn cứ chính để đánh giá hiệu quả hoạt động của của
                                            cửa hàng khác. Tìm kiếm và áp dụng linh hoạt công thức của cửa hàng hiệu quả
                                            cho các cửa hàng khác trên hệ thống.</p>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.15&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8TJ09BezhrdhbbibpEdDhyQEg6sw2AawPB1_7lZRdfQV30aB-Fl8IpKB9tlJBYz6xmoFgL51Tfu_xqKNIbaI6dv4O16bdWi7l0KU1NGdE1eocFWOE-eR2R970&disp=emb&realattid=ii_k5eyvr6o14"
                                                alt=""></div>
                                        <p>Như trên hình là so sánh chỉ số khách vào mua sắm cửa 2 vùng miền Bắc và Nam
                                            cửa một đơn vị . Biểu đồ thể hiện mức chênh lệch giữa các ngày, bảng biểu
                                            thể hiện chị tiết sự chênh lệch giữa các vùng miền. </p>
                                    </li>
                                    <li>Báo cáo so sánh theo thời gian
                                        <br>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.16&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ_cb7XOr31UWIlPTlIaXuo7_XyfT8lTdQXy9tZe6-pCMeZhRmXdeARy7_9Bjy-qdbwgQZn0DGhehUL_aOpcLgo_PxVlbeYJ0RmhJzlTsNsQOSfOG85RBKLml54&disp=emb&realattid=ii_k5eyvvk515"
                                                alt=""></div>
                                    </li>
                                    <li> Báo cáo so sánh theo chỉ số
                                        <br>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.17&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ99gx_UBvL8a-Neh9DylUy0WwhnqlmsGBphwMULLuWKmP8-lzPySbSu64S7ryiihz4JsJJe3BYyei3rAWfJylWrZ60PTQwWXzwiVNmFzsnRVF3PlmGpU_19ZbM&disp=emb&realattid=ii_k5eyvzr316"
                                                alt=""></div>
                                    </li>
                                </ol>


                                <h3 id="create-firebase-project"><strong>5</strong>. Báo cáo xu hướng</h3>
                                <p>Bao gồm so sánh theo cửa hàng, theo thời gian và chỉ số. Đánh giá xu hướng hoạt động
                                    của các cửa hàng, chỉ số theo từng thời điểm, đánh giá giữa ngày làm việc và ngành
                                    cuối tuần để tìm ra xu hướng mua sắm của khách hàng từ đó xây dựng chiến lược
                                    Marketing và hệ thống hóa nhân sự.</p>
                                <ol>
                                    <li> Báo cáo xu hướng theo cửa hàng
                                        <br>
                                        <div style="text-align:center">
                                            <img src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.15&permmsgid=msg-a:r5069550112429686371&th=16fa80bd8fe75547&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ8TJ09BezhrdhbbibpEdDhyQEg6sw2AawPB1_7lZRdfQV30aB-Fl8IpKB9tlJBYz6xmoFgL51Tfu_xqKNIbaI6dv4O16bdWi7l0KU1NGdE1eocFWOE-eR2R970&disp=emb&realattid=ii_k5eyvr6o14"
                                                alt=""></div>
                                        <p>Như trên hình là so sánh chỉ số khách vào mua sắm cửa 2 vùng miền Bắc và Nam
                                            cửa một đơn vị . Biểu đồ thể hiện mức chênh lệch giữa các ngày, bảng biểu
                                            thể hiện chị tiết sự chênh lệch giữa các vùng miền. </p>
                                    </li>
                                    <li>Báo cáo xu hướng theo chỉ số
                                        <br>
                                        <div style="text-align:center"><img
                                                src="https://mail.google.com/mail/u/6?ui=2&ik=6c74b81050&attid=0.21&permmsgid=msg-a:r-6098982378884044988&th=16fa8286403fd75c&view=fimg&sz=s0-l75-ft&attbid=ANGjdJ9flNman-2emorCJGhG9lHTuy68ZhuxYc7uMvRzFGY2jVpgcOALFVwocKs3Qv2Bh8U-OW4s4E5Fsa6XLoywgrkRIStvNXmuxm0GcfFhTLaGWTmejcScmlmToYU&disp=emb&realattid=ii_k5f008nm20"
                                                alt=""></div>
                                    </li>
                                </ol>
                                {{-- Footer --}}
                                {{-- <devsite-page-rating position="footer" selected-rating="0" hover-rating-star="0">
                                </devsite-page-rating>
                                <devsite-feedback project-name="Firebase" product-id="719752" bucket="" context=""
                                    version="devsite-webserver-20200102-r01-rc03.423746209109273667"
                                    data-label="Send Feedback Button" track-type="feedback"
                                    track-name="sendFeedbackLink" track-metadata-position="header"
                                    project-feedback-url="#" project-icon="#" project-support-url="#"
                                    project-support-icon="#">
                                    <button>Send feedback</button>
                                </devsite-feedback> --}}

                        </article>
                    </article>
                </devsite-content>
            </main>
            <devsite-footer-promos class="devsite-footer">
            </devsite-footer-promos>
        </section>
    </section>
    <devsite-analytics>
        <script type="application/json" analytics>
            []
        </script>
        <script type="application/json" gtm>
            {"parameters": {"freeTrialEligibleUser": "False", "language": {"requested": "en", "served": "en"}, "projectName": "Firebase", "scriptsafe": null, "signedIn": "True", "internalUser": "False"}, "tags": ["GTM-N84485"]}
        </script>
    </devsite-analytics>
    <firebase-gtm></firebase-gtm>
    <script>
        (function(d,e,v,s,i,t,E){d['GoogleDevelopersObject']=i;
    t=e.createElement(v);t.async=1;t.src=s;E=e.getElementsByTagName(v)[0];
    E.parentNode.insertBefore(t,E);})(window, document, 'script',
    'https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb/js/app_loader.js', '[4,"en",null,"/js/devsite_app.js","https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb","https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb/firebase","https://firebase-dot-devsite-v2-prod.appspot.com",null,null,["/_pwa/firebase/manifest.json","/_static/images/video-placeholder.svg","https://www.gstatic.com/devrel-devsite/prod/v45a7fc87bcb751eb7763ee8391250bbc83d44548f17311eb565c4ad1b50041cb/firebase/images/favicon.png","https://gm1.ggpht.com/CDzG_Ar-WwyJFJkaPBawS41DUSzmO7N9GLMxA39Cycvr7jFKlorkB6vhxVYuks4ZbEE6-OwutMvHbTdyCO_wajiUtgUVt1bXqrTqCML7k9Ft0dGq7Uam_ZGTfz9K6-GIhDSjiCSqYmpE_zxpRMPVvYLjBLn2tE1k5kYg_Bj543uD2NfcS4IyT955vEyHgDw9qQEh3NTH5H-1Xt45BTpQUTqr-gnSwmNpwKbJib_XFbd6lgIXepPOdHvqjZi02m3LJyJmM-oBrZCb_q2gLuMo1yKUc-4W50Mx3FQklY9c2VoiAaKJAIakay3hnbaYkOoj5-crv8gO4BquIL2WN6BAJRtzS54wMwdwhaCRMATLPZx2X76S9RnlYk7XMJETCB9E9jooudMCEkzR1q_GuUG7JNRlEXBrW-WloDV_GbKUE1ssVJ1FDEHUwwVUz_RvLg1ULdny-xbEYxpmEXiPZgQDccuz4qKdV_-4gt3PvuIg6FcuGLrQWc7saEt8yXqu8CrvrirzZO5bc98OHBeEVx6uXbSNybvLxSrClIaJu2v3AgEzUSgza030Ro6ROHsUxujS5HRQusVhxYyH5Pgs4O0fspIbgxE-B_cTTlo6_ebK7zPM60Tu_K0iE9Hm6DMnOA5WYeYc2233X5e48z2IWBQiX2U6h8oq45kb1EiNyrlM5TDLrIJ_303dLef2zvgAVAOSnvUGp84nhuF-Pl17RfremMMpZdbQ_2lNn535nwR9i8qzMSfZSq18z9uo_AFq=s0-l75-ft-l75-ft","https://fonts.googleapis.com/css?family=Google+Sans:400,500|Roboto:400,400italic,500,500italic,700,700italic|Roboto+Mono:400,500,700|Material+Icons"],1]')
    </script>
</body>

</html>