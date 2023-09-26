<?php
require('dnlib/load.php');

$action->helper->route('/',function(){
 global $action; 
$data['title']='RESUME FORMETS - Online';

 $action->view->load('header',$data);
 $action->view->load('nav',$data);
 
 $action->view->load('footer'); 
    
}) ;

$action->helper->route('action/createresume',function(){
    global $action; 
    $action->onlyForAuthUser();
    $resume_data[0]=$action->session->get('Auth')['data']['id'];
    $resume_data[1]=$action->db->clean($_POST['name']);
    $resume_data[2]=$action->db->clean($_POST['headline']);
    $resume_data[4]=$action->db->clean($_POST['objective']);

    $contact['email']=$action->db->clean($_POST['email']);
    $contact['mobile']=$action->db->clean($_POST['mobile']);
    $contact['address']=$action->db->clean($_POST['address']);
    
    $resume_data[3]=json_encode($contact);
    $resume_data[5]=json_encode($_POST['skill']);

    $education['collage']=$action->db->clean($_POST['collage']);
    $education['course']=$action->db->clean($_POST['course']);
    $education['duration']=$action->db->clean($_POST['duration']);
    
    $resume_data[7]=json_encode($education);
    
    
    $experience['company']=$action->db->clean($_POST['company']);
    $experience['jobrole']=$action->db->clean($_POST['jobrole']);
    $experience['w_duration']=$action->db->clean($_POST['w_duration']);
    
    $resume_data[6]=json_encode($experience);
    $resume_data[8]=$action->helper->UID();



    $run=$action->db->insert('cv','user_id,name,headline,objective,contact,skills,experience,education,url',$resume_data);
      if($run){
        $action->session->set('success','resume created ');
        $action->helper->redirect('home');
    }else{
    $action->session->set('error','something went wrong,try again ');
    $action->helper->redirect('home');
}
    

   }) ;
   
//for delete
   $action->helper->route('action/deletecv/$url',function($data){
    global $action; 
 $url = $data['url'];
 $action->db->delete('cv',"url='$url'");
 $action->session->set('success','resume deleted !');
  $action->helper->redirect('home');
       
   }) ;
   

//for logout

$action->helper->route('action/logout',function(){
    global $action; 
  $action->session->delete('Auth');
  $action->session->set('success','logged out !');
  $action->helper->redirect('login');
       
   }) ;
   

   //for details
   $action->helper->route('resume-details/$cvtype',function($data){
    global $action; 
    $action->onlyForAuthUser();
   $data['title']="Resume Details";
   $data['myresume']='active';
   
    $action->view->load('header',$data);
    $action->view->load('nav',$data);
if($data['cvtype']==1){
    $action->view->load('resume_details1');
}else{
    $action->session->set('error',"Invalid Resume type");
    $action->helper->redirect('select-template');
   
}
   $action->view->load('footer'); 
       
   }) ;

   //for template

   $action->helper->route('select-template',function(){
    global $action; 
    $action->onlyForAuthUser();
   $data['title']="Select Resume Template";
   $data['myresume']='active';
   
    $action->view->load('header',$data);
    $action->view->load('nav',$data);


    $action->view->load('template_content');
   $action->view->load('footer'); 
       
   }) ;



//for resumeformets

$action->helper->route('cv/$url',function($data){
    global $action;
    $cvdata=$action->db->read("cv","*","WHERE url='".$data['url']."'");
   
   if(!$cvdata){
    $action->helper->redirect('home'); 
   }
    $cvdata=$cvdata[0];
    

   $data['title']=$cvdata['name'];
   $data['type']=1;

   $data['cv']=$cvdata;

if($data['type']==1){

    $action->view->load('rf1',$data);
}else{
    $action->helper->redirect('home');
}
       
   }) ;


//for home   
$action->helper->route('home',function(){
    global $action; 
    $action->onlyForAuthUser();
   $data['title']='Home';
   $data['myresume']='active';

   $data['cv']=$action->db->read('cv','*',"WHERE user_id=".$action->user_id());
   
    $action->view->load('header',$data);
    $action->view->load('nav',$data);
    $action->view->load('home_content',$data);
    $action->view->load('footer'); 
       
   }) ;



//for login

$action->helper->route('login',function(){
    global $action; 
    $action->onlyForUnauthUser();
   $data['title']='LogIn- Online';
   
    $action->view->load('header',$data);
    $action->view->load('login');
    $action->view->load('footer'); 
       
   });


   $action->helper->route('action/login',function(){
    global $action; 
    $error=$action->helper->isAnyEmpty($_POST);
    if($error){
   $action->session->set('error',"$error is empty !");
    }else{
        $email=$action->db->clean($_POST['email_id']);
        $password=$action->db->clean($_POST['password']);
    $user=$action->db->read('users','id,email_id',"WHERE email_id='$email' AND password='$password' ");
    if(count($user)>0){
        $action->session->set('Auth',['status'=>true,'data'=>$user[0]]);
        $action->session->set('success','logged in');
        $action->helper->redirect('home');
    }else{
        $action->session->set('error',"incorrect email/password");
        $action->helper->redirect('login');
    }
    }
   
   });


//for signup

$action->helper->route('signup',function(){
    global $action; 
    $action->onlyForUnauthUser();
   $data['title']='SignUp - Online';
   
    $action->view->load('header',$data);
    $action->view->load('signup');
    $action->view->load('footer'); 
       
   });

   //for action signup
   $action->helper->route('action/signup',function(){
  
    global $action; 

    $error=$action->helper->isAnyEmpty($_POST);
  if($error){
    $action->session->set('error',"$error is empty !");
    $action->helper->redirect('signup');
  }else{
    $signup_data[0]=$action->db->clean($_POST['full_name']);
    $signup_data[1]=$action->db->clean($_POST['email_id']);
    $signup_data[2]=$action->db->clean($_POST['password']);
$user=$action->db->read('users','email_id',"WHERE email_id='$signup_data[1]'");
    if(count($user)>0){
        $action->session->set('error',$signup_data[1]." is already ragistred !");
        $action->helper->redirect('signup');
    }else{
        $action->db->insert('users','full_name,email_id,password',$signup_data);
        $action->session->set('success','account created !');
        $action->helper->redirect('login');
    }
    
  }
  
   });
   
   

/*

if(!Helper::$isPageIsAvailable){
   $data['title']='No page found';
    $action->view->load('header',$data);
    $action->view->load('nav',$data);
    $action->view->load('no_found');
    $action->view->load('footer');
}
*/