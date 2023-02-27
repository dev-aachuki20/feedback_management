<?php
    require_once __DIR__ . '/vendor/autoload.php';
    $filename = './file.pdf';
    $mpdf = new \Mpdf\Mpdf();

$html = '<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Test</title>
     

        <style>
            * {
                margin: 0;
                padding: 0;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                outline: none;
                list-style: none;
                word-wrap: break-word;
                font-size: 14px;
            }

            
            .col-md-4 {
                display: inline-block;
                margin-left: 1.5%;
                margin-right: 1.5%;
                margin-bottom: 1.5%;
                width: 31.33%;
                float:left;
            }
            .metter-outer.active {
                padding: 5px;
                border: 1px solid #000;
                background: #eee;
                height: 250px
                display: inline-block;
                padding-top: 10px;
                padding-bottom: 10px;
                text-align: center;
            }
            .circle-bg {
                    width: 258;
                    height: 125px;
                    background: #eee;
                    background-image: url("./upload_image/chart.png");
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: contain;
                }
            .circle-frame {
                    padding-bottom: 5px;
                }
            .metter-outer.active .meter-clock {
                   
                    background-position: center;
                    background-size: contain;
                    height: 250px;
                    width: 250px;
                    background-repeat: no-repeat;
                    
                    transform-origin: bottom;
                    margin-top: -100px;
                }
                
            .row {
                    display: -ms-flexbox;
                    display: flex;
                    -ms-flex-wrap: wrap;
                } 
                .top-content h4 {
                    margin-bottom: 10px;
                }

                .top-content p {
                    font-weight: 600;
                    margin-bottom: 7px;
                }

                .top-content span {
                    margin-bottom: 15px;
                    display: block;
                    font-weight: 600;
                    font-size: 25px;
                }  

                .bottom-content h4 {
                    margin-top: 40px;
                    margin-bottom: 10px;
                }
                .header img {
                    width: 13%;
                    margin-bottom: 10px;
                    margin-top: 10px;
                }

                .header {
                    width: 100%;
                    text-align: center;
                }

                .header .title h3 {
                    font-size: 20px;
                    padding: 12px;
                    border: 1px solid #eee;
                    border-right: none;
                    border-left: none;
                }

                .title {
                    margin-bottom: 10px;
                }
                .bottom-content {
                    margin-top: -120px;
                }
        </style>
    </head>
    <body >
        <div class="report-container">
            <div class="row">
                <div class="header">
                    <img src="./upload_image/chart.png" alt="" height="80">
                    <div class="title">
                        <h3> United States Minor Outlying Islands United States</h3>
                        <h3> United States Minor Outlying Islands United States</h3>
                    </div>
                </div>
            </div>   
            <div class="row">';  
            $count = 0;
            //i = percentage
            for($i= 0; $i <=100;$i++ ){
                
                $degree = 182 - (ceil((180*$i)/100));
                
                if($count <6){
                $html .=  '<div class="col-md-4">
                        <div class="metter-outer active">
                                <div class="circle">
                                    <div class="top-content">
                                        <h4>United States Minor Outlying Islands United States</h4>
                                        <p>Survey ID : '.$degree.'</p>
                                        <span>'.$i.'%</span>
                                    </div>                                  
            
                                    <div class="circle-frame">
                                        <div class="circle-bg"></div>
                                        <div class="" style="">
                                            <img src="./upload_image/niddle/180_niddle/niddle_with_circle/Asset '.$degree.'.png" height="190" style="margin-top:-107px" class="meter-clock meter-clock-overall_1"/>
                                        </div>
                                    </div>
                                    <div class="bottom-content">
                                        <h4>Total Surveys : 20</h4>
                                        <h4 style="margin-top:5px">Contact Requests : 20</h4>
                                    </div>
                            </div>
                        </div>
                    </div>';
                }else{
                    $html .=  '
                    
                    <div style=" margin-left: 1.5%; margin-right: 1.5%; margin-bottom: 1.5%; width: 31.33%; float:left;">
                    
                        <div style=" height:90px;width: 31.33%;">
                        </div>
                        <div class="metter-outer active">
                                <div class="circle">
                                    <div class="top-content">
                                        <h4>United States Minor Outlying Islands United States</h4>
                                        <p>Survey ID : '.$degree.'</p>
                                        <span>'.$i.'% </span>
                                    </div>                                  
            
                                    <div class="circle-frame">
                                        <div class="circle-bg"></div>
                                        <div class="" style="">
                                            <img src="./upload_image/niddle/180_niddle/niddle_with_circle/Asset '.$degree.'.png" height="190" style="margin-top:-107px" class="meter-clock meter-clock-overall_1"/>
                                        </div>
                                    </div>
                                    <div class="bottom-content">
                                        <h4>Total Surveys : 20</h4>
                                        <h4 style="margin-top:5px">Contact Requests : 20</h4>
                                    </div>
                            </div>
                        </div>
                    </div>';
                }
                
                $count++;
            }
           $html .= '</div>
       </div>
    </body>
</html>';
    //   echo $html; die;
    $mpdf->WriteHTML($html);
    //$mpdf->Output($filename,'F');

    $mpdf->Output();
?>