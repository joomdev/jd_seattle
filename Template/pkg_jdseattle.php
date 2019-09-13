<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2019 JoomDev.
 * @license   GNU/GPLv2 and later
 */
// no direct access
defined('_JEXEC') or die;

class pkg_astroidInstallerScript {

   /**
    * 
    * Function to run before installing the component	 
    */
   public function preflight($type, $parent) {
      
   }

   /**
    *
    * Function to run when installing the component
    * @return void
    */
   public function install($parent) {
      $this->getJoomlaVersion();
      $this->displayAstroidWelcome($parent);
   }

   /**
    *
    * Function to run when un-installing the component
    * @return void
    */
   public function uninstall($parent) {
      
   }

   /**
    * 
    * Function to run when updating the component
    * @return void
    */
   function update($parent) {
      $this->displayAstroidWelcome($parent);
   }

   /**
    * 
    * Function to update database schema
    */
   public function updateDatabaseSchema($update) {
      
   }

   public function getJoomlaVersion() {
      $version = new \JVersion;
      $version = $version->getShortVersion();
      $version = substr($version, 0, 1);
      define('ASTROID_JOOMLA_VERSION', $version);
   }

   /**
    * 
    * Function to display welcome page after installing
    */
   public function displayAstroidWelcome($parent) {
      ?>
      <style>
         .astroid-install {
            margin: 20px 0;
            padding: 40px 0;
            text-align: center;
            border-radius: 0;
            position: relative;
            border: 1px solid #f8f8f8;
            background:#fff url(<?php echo JURI::root(); ?>media/astroid/assets/images/moon-surface.png); 
            background-repeat: no-repeat; 
            background-position: bottom;
         }
         .astroid-install p {
            margin: 0;
            font-size: 16px;
            line-height: 1.5;
         }
         .astroid-install .install-message {
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
         }
         .astroid-install .install-message h3 {
            display: block;
            font-size: 20px;
            line-height: 27px;
            margin: 25px 0;
         }
         .astroid-install .install-message h3 span {
            display: block;
            color: #7f7f7f;
            font-size: 13px;
            font-weight: 600;
            line-height: normal;
         }
         .astroid-install-actions .btn {
            color: #fff;
            overflow: hidden;
            font-size: 18px;
            box-shadow: none;
            font-weight: 400;
            padding: 15px 50px;
            border-radius: 50px;
            background: transparent linear-gradient(to right, #8E2DE2, #4A00E0) repeat scroll 0 0 !important;
            line-height: normal;
            border: none;
            font-weight: bold;
            position: relative;
            box-shadow:0 0 30px #b0b7e2;
            transition: linear .1s;
         }
         .astroid-install-actions .btn:after{
            top: 50%;
            width: 20px;
            opacity: 0;
            content:"";
            right: 80px;
            height: 17px;
            display: block;
            position: absolute;
            transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
            background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAARCAYAAADdRIy+AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OERDRjlBMjY0OTIzMTFFODkyQTI4MzYzN0ZGQ0Y1NTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OERDRjlBMjc0OTIzMTFFODkyQTI4MzYzN0ZGQ0Y1NTMiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4RENGOUEyNDQ5MjMxMUU4OTJBMjgzNjM3RkZDRjU1MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4RENGOUEyNTQ5MjMxMUU4OTJBMjgzNjM3RkZDRjU1MyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PvXGU3IAAADpSURBVHjarNShCsJAHMfxO1iyyEw+gsFoEIMKA5uoxSfwKQSjYWASq4JVEKPggwgmi2ASFcMUdefvj3/QsMEdd3/4lO32Zey2SaWU0JwsLOEGndRVFNTkw0N9Z562ziRIAog4OnMRJDV4c3TsIki6EHM0dBEkbfWbgYsgqcKRo3065mGjm1CHM0ihP084wA7yMIRIokonPOFoKNjjO4zptTS4ltZuoQUVPhbaPsMC7PkZjmw3pQx3jk1sdzn4i01t38MiXDi2sP1SSnDl2Mr2W87BiWNryCStkwb/Qx82/D9swCtp0UeAAQDi4gvA12LkbAAAAABJRU5ErkJggg==') no-repeat;
            -webkit-transition: all .4s;
            -moz-transition: all .4s;
            transition: all .4s;
         }
         .astroid-install-actions .btn:hover{
            transition: linear .1s;
            box-shadow:0 0 30px #4b57d9;
         }
         .astroid-install-actions .btn:hover:after{
            opacity: 1;
            right: 20px;
            margin-left: 0;
         }
         .astroid-support-link{
            color: #8E2DE2;
            padding: 30px 0 10px;
         }
         .astroid-support-link a{
            color: #8E2DE2;
            text-decoration: none;
         }
         .astroid-support-link a:hover {
            text-decoration: underline;
         }
         .astroid-poweredby{
            right: 20px;
            width: 150px;
            height: 25px;
            bottom: 20px;
            position: absolute;
            background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAAZCAYAAADT59fvAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NjBBMzcwNEU0N0YzMTFFOEE0ODFCNkJDMkNBMDVFNDIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NjBBMzcwNEY0N0YzMTFFOEE0ODFCNkJDMkNBMDVFNDIiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo2MEEzNzA0QzQ3RjMxMUU4QTQ4MUI2QkMyQ0EwNUU0MiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo2MEEzNzA0RDQ3RjMxMUU4QTQ4MUI2QkMyQ0EwNUU0MiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgETteYAAA5xSURBVHja7Ft5eBRVEq+eI8lMJglJIBAMaPAKIKfycQobEURZVDyWFVdFdFkRZNUvn66rCwjriSgiriwIHqyA68quBwjiwQokSKJCAJEzIYSQ+5pMMpOZ7t563TXJm57ungniH7rUZ/E63a9fv+P3qn5VbxTknTOTQBZHgywNxLIZyxrUXLw+jCUAyPiflA4g1YMktUCaDK073FAx6zTYutgBrGqVKKQLalfUA7pvSAKILTZImXgcnCNqAdx0X0C1cPUEUtCUAleHtW6F0L4JOvU6ci8oFuiYRFtf+02I4u+zVUeI8L7RuEzq2vBpA5afgCyXoY7HFb4fdTreQ9RAJd5z4epkYtmC5Was/xe836zpShxqi077rI0U1AGo16Pi+7A/vJMI1op4SB5fDM6rEFRVcE5+5mKjMoD6DWIEFZ5BEF2I4BqKVutBtFZDUNF+SHYQpYfBYp0gOIShWC9BtFpng1XoDZIsWSUREQE1BDqRQOVAjUXthNoKgpwnBywgNdlD0C55bRDb3Q3xQxFRTWRlhHOL80sAllaO4cqiwloEVy6u9HAE1zEQRQmcwiUtJdIHTvCIqeWnsvBZqSTENDckpXb2OF2IHbnVIolefJdpo2SxNTtaPYmWZljvhsQaW4IPXEMqEU0EIFTJb4WkMafA2sUPUEcu7Jz8rEWQd6Lnk9FgSSKonAqvZWW18bKFWY7eeL8QrVUTOrUab5lssd+Zn9kU6FSUP3xqZXNSZ0dqeUHLoG+3Vjp99fUeZ5rN7UqyyILFbxUDcbE+j1MWrR+Xd09f3rVziewaVQn281pCgKXYtlaCoh6/Ocexfo4cy0CY0WHgEiwHcXXeBEG8V06IrYx7c29ahS0TFq5a0XV3ZlUPcLtt55+6sS6zZFpq9rbVZYP37ilOqyqvYgBtTExuSPBUbJ7+2Ib8iy/fAY/v/itAKrZdy3UqCALLOff3y3aFAt4KeAD8zFoJZMXgJbDL04Xq6rTGok4x9+Wsg8+7bnc68l4GqU95a2Ov8S0nM976ftOEbKlH8RFp4L7viiWLP+/rIcMPpNc1yetH9O+1oGDHccUiNZ0RgJDzQTJBsBH1CPHCH22xUbMYXySIM0d89AzbYlzSF2XdOLLR0v+BKwzQPGPZjAZHRA4ucGQnBjZBbeW1+3YPaMzxT3LmvVLqj5c6eWOy6pI9T650Z5w3teT8urm7ypICcQ0um0+yQr1NBFeLDDHp5UUL3s3Ljj1fPPE8ttOISzgbW6w3tFhq2Q31Pry+Bp8NpMVgz2RUtKKQh+USJcrsuCschDoDr0ei9uPexQAD9qBuRV1MQNN3leEyBxVNPLwWwRWylMsC1EdQG0zqTcLvzMDS00YIBAW49bSxtqHujcIVTsR793HtqHPYLvH49wksH1L63/7+jaizlcwAKO83RuEK70S9BbWYgOVXjRcDU9MJ1UoJFi0E75G98Lps850WXOWBZxdPTV6wcfTezlDbSxi/U/TMXb3/wvpVzRnibSfx9S6tcrnFIjXWFHa6ZM3I+oKv1+YOmYx2YQMNKQP1VJjdbAfW7fjvItT0CByLTfRTWC7sALAW4r+PK1fmHOskTfb7YROoz5veobTKZRHqson/B2ov1DKTemxMT0QAzad471ksvzSpMx/vzYuCY8UrEX37s36ohXQ9E3V5hHExgLLxJKIusymtWuNUot6C4AwguKxo1WVRk8CETUKs3CQIlmRwOKofW7BW/OjABQkFxRd93vNQ5oTmclfdoZSpNe76bbmdbVP2JcFV8VbJsddrBf+UiiWMoLcqhl9QUp9iGwCCyJe5CYWQCf0X6kdsF9B7DJTX0AIl0O5ni3R3FG7v36g30N+sH+tRN6JWE7wvUna4DJOxdg/6dg7q4pCW9NMhzKX2JT1g0o/fktu8NARY4W0GLQRL4Wyg6yTU7vStzqjjSf+IujSKdv7DzTqQK2ZzeLrNjbe/vw/1FdQHqP3lEeZ3BoGKWcZ5NqWRVvx2I1pXCd2+YFdJe7icxmX9VsnSlybZIavBN2fKZz1ve+7SfP+J3gVxRf3ShdFf+E5vX3VjqbTCcwHkbIxzLIKxNTvhupPvMesh6y61JQRUORyo8sm97NLpy7uoGAnAC6iTUacRUOaYDPw9DlTryGoVaer8F3UV6jDUlWR92DcqyMoYgYAtdm9ud8826EM3xcWpYx8XwdIE5QgtGi9pBKi5qBejvkxue40J8Bl3vLeDVOk1AlYWzd0HJlw9OGY2f7UW8GGIVvMd2gKf6jtkSjvoqpivlFa/DarixWuHHoy/xHV8YDU4j8Qtu/1gzPtj3nZmpK1Hm3akqutGsOG+nF00H+yBVv34M9QNXaokZ1U5qFgOfVAF5TjqTWRVgCZgvMEi3Yp6M10zKzVVB1S8sO+O4+qsUNyysQyjZDBQ28kG9e5R2KoqV0e5uHYdx1NJQB9LwAuCoGcH24mUmmDr8Bld32/y1iTyGoyov6p6SndRe2uMxCvAMtRCNUoU48AdY0tKrWu+ZeS+/s3QZPOd6OFPmPl8Wez9D35o3Z51uGrPJFhQmAMjGj5TabfReaLcpg9zbIsBJtqDHcbHSmlSHg0Ba/s3H6eSkdTfRdluOYFRItDMMqk7lrtOJnenFwk+wP09IAIQImWngjxwYhvHARy/sQSiikLD1+kFKsfj9y40CF4epnIL6mEVWH6PStTNARW0ZJuxxEgmEA8+fMkveB+4Y7P115mHR5dCTHURtMwVvhkw1vbEIpBmToJ1z7eAz2ZX94r5dDlRr6NrRvB/4ABnnlAUlLB9Cd0dg5qpqTmIFpG1tRSnVlSmV4rQtirfcRZxYsiOb+8fq30VZ0XBAISTKSJ0UzTILNcVJgtqvuhCiKtc07YhBXAZwJABrw8pm4/+pIPpnpEz3kLzwORBnedsDKPoeinH7SnLLgWi0UoE1ywlS28JpEKN09Etvapq3aKlfedPfefiEQP2P1MEtvoahZuK8EbpDdDU4DAGVmieKoOuN2uChvBJDw91t3Cx4DBN7QHc9RdhbetxPkETeanSVwe0QNwqi8DyBwJOX2zjSk07d3MW4CW6Ht7hBJE+AD/mOFxvgzeDQQXTPUqqQtVviDvFmXzjVSrvAjXFzctMKndzc2WSeTce2TsKy5dEnCC5J1QkJrpS6mBezso75jXZjn6ZO2hZrN0LYowVYot8kGT1RJM25DtbErbYoiYlIYQNvoIWNJGILS8pVHqIm2hOGLi29XMzxRxHYZHiMc3zkVSWER/5goguI9zbudB9HMfX+tH1r844rRsqJRpi31FxRXjO+NyTqOdRTuspLmiZRtfP8GtiO8NRYYQlo5WQbgGreAVUJ2Sg9UqEuOabs4fni2g3nkb8y8oyH6DFM8+2e4FPxUbHMLRRSTCbq4Wxj6tjN21bP5qK5a49Ov25kspcKt8gYDGe+Cds8xQlKIFmo5wLBAaRFSz6kYluvo9+gzo/oE7nLHtwxHGg/iLFa9K+j6Lk+QSsF0H9mVQObUfW/03mRzrRC8uPrMaurUZwqVGjN0aAkm4uvCeDE/FRW4OL4IkGGKV0xBFDuaRQt2fRLL4UtriZxCGAi5KCcpKb/O5E4FWxatqRdIHch/6u0WnbylmsbVR+QFyLRUkTKPyeQs9epvIQBSddyB12DFjhG6C3zni10qCcVpy5vE7AyiBOtZWAymQZrV+EHPKZn7whlZXc4MRRu5sA6rzRQpcBq6AtXBdMj04ghNjLbWE80GLlacCRyyUJo2+7XW6j8uu2Y6h2uYwLs7/i7i/hotvrydUzN/wW3W/m+N6VURN4bcTbXv8uKvfhvUMG7dgh2h8kCbrKTkrWcnNyHSVs62nzwE8DLGUXYQ/iEUluBO9h/J5X5L8ghU1P6M57g4syZnSAxGbTGVUwcdqseV6tHMuoX74HdYgpsELlUYqagkc2WgmColDDc1jdJrJYazme0sp9ewuXA/sxMoNrYzn8tAfbL3LAWsONteHsA0s5IMCZisPNYMWyEtf1WC16ekl1bJKu728N2XXqZL/JHYX8HfU3UXy9H4HJpnAEGRa1ucpQq/U0fclBrqpPFG2zXNSzdL2fAwgv48KiTXUstXR8YiEXLdOYePmS7rOQ/wKTfogQPAILl1lcu4URjl3M2olktYPCIsjPiZelaFMMZxdYDrsKqGrkUgfROBxBS+WXVUYjh1iXbPrrNAR/NRAcgBqNBShD3sBZnxWUitBKJzpCyCWeEkyUlhiY9KOKG1T7kU6Z9T/rhM7B58+BeuwDZOon65B9V1tUJ2M0GL4Yr3LXHwYTh5pocx+NfpTJDMdQ0jWFuOTlBKjtxG2Cid/r27aUYNhOCs0dr2kcSCKlNYDmBrhTjMNG0dSZubwY9msIxEI1RvnliIV6X2hMJyMpFZSoYRct4EM04A9D7Fj4EcJEsg4sK/17ypR/RVGNl3I12VzWupXqbYjQ8/VkPf4G6sErC5nnUNsl1KdelOxM5haffV/vN1qDKb3BoqN8nee76P4QnIvXDSLRXWSxxgB/Fhm66fuTxYwhINh0vjMtJCgxbwc0TC2egolRWgJuIFupPjuCW2wWpndMmNsTcQ3qkUJUoMWv9bZTQyGk3U+I3N3EvX2Mcy9GspMWbRm5I+a+riEFHXfyGBHraISRzO+JKwyjTPitBnXfBvVnM7UGm2AKFxzUGrTxqQIs9RcUerKDOBI7HH6kzZKTL+CA0V0nIi9UovJ2bmqUmnFwT4zOOy/XJfbG/1PLJooOC84esOz4fTdap+/L1CEHjzblsOHl0C7oTfyKme5XiExHkhoiiGxHXE2Lk0ZfqqGMcS6Bt6OSRyH+BOrfYNoAEuWYCogz5ZssmKDm8uBbMD8o/ye1aeRYPqKIzmaQlDxBYAsSBpESwUdN0gpaWUd163X6IZNLb4yQx9LKSnxzm1mF/wkwAM2LDe1DvOR0AAAAAElFTkSuQmCC') no-repeat 0 0;
         }
         .astroid-poweredby a{
            bottom: 0;
            display: block;
            font-size: 0;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
         }
         .astroid-poweredby span{
            font-size: 0;
         }
      </style>
      <div class="astroid-install">
         <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQEAAAA9CAYAAACtI9yfAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RTVCRjFDOTQ5RkYxMTFFODhEOTJCRkJEQUY2RDMzN0QiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RTVCRjFDOTU5RkYxMTFFODhEOTJCRkJEQUY2RDMzN0QiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpFNUJGMUM5MjlGRjExMUU4OEQ5MkJGQkRBRjZEMzM3RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpFNUJGMUM5MzlGRjExMUU4OEQ5MkJGQkRBRjZEMzM3RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PukAGH0AABgwSURBVHja7F0HfFRV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" alt="astroid-logo" />
         <div class="install-message">
            <h3>JD Seattle - Free Multipurpose Business Joomla Template
               <span>v1.5</span>
            </h3>
            <p>Stunning Free Joomla 3.9 Template for Business, Corporate, and Agency Sites. Packed with premium feature, a totally free them for you.</p>
         </div>
         <div class="astroid-install-actions">
            <a href="index.php?option=com_templates" class="btn btn-default">Get started</a>
         </div>
         <div class="astroid-support-link shake-trigger">
            <a href="https://www.joomdev.com/documentation/seattle" target="_blank">Documentation</a> <span>|</span> <a href="https://github.com/joomdev/jd_seattle/releases" target="_blank">Changelog</a> <span>|</span> <a href="https://www.joomdev.com/forum/jd-seattle" target="_blank">Forum</a> <span>|</span> <a href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTBBVpJqe3SdJ34A6VvicXqM" target="_blank">Tutorials</a> <span>|</span> <a href="https://www.joomdev.com/about-us" target="_blank">Credits</a>
         </div>
         <div class="astroid-poweredby">
            <a href="https://www.joomdev.com" target="_blank">
               <span>JoomDev</span>
            </a>
         </div>
      </div>
      <?php
   }

   /**
    * 
    * Function to run after installing the component	 
    */
   public function postflight($type, $parent) {
      
   }

}
