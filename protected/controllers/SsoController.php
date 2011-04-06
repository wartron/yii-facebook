<?php

class SsoController extends Controller
{

	public function actions()
	{
		return array();
	}


	public function actionIndex()
	{
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function actionFacebookLogin()
	{
		//JUST TO BUILD A SESSION	
		$isguest = Yii::app()->user->getIsGuest();
		//JUST TO BUILD A SESSION
		
		//get facebook api object
		$facebook = YII::app()->facebook->getFacebook();
		
		//get fb connect url
		$url = $facebook->getLoginUrl(array('next'=>"http://xntix.com/?r=sso/FacebookCallback"))."&req_perms=email,publish_stream";
				
		//send to facebook connect
		$this->redirect($url);
		
	}
	

	 public function actionFacebookCallback(){	 
		//JUST TO BUILD A SESSION	
		$isguest = Yii::app()->user->getIsGuest();
		///if already logged in make this fb accout on the user
		
		//get facebook api object
		$facebook = YII::app()->facebook->getFacebook();
	
		//get facebook session
		$fsession =  $facebook->getSession();
		
		if($fsession) {
				try {
						//get uid
						$uid = $facebook->getUser();
						//get user info for registering/loggining
						$user_info = $facebook->api('/me');
				} catch (FacebookApiException $e) {
						error_log($e);
				}
		}
		
		//grab matching user for fbid if exists
		$olduser=User::model()->notsafe()->findByAttributes(array('fbid'=>$user_info['id']));
		
		//i should make something more interesting here.
		$fakepassword = "salt".$user_info['id'];
		
		//see if a user account with the fbid exists
		if($olduser ===null){	//new facebook user
		
			$model = new User;			//make the user module
			$profile=new Profile;		//since im using the yii user module i have a profile model as well
			$profile->regMode = true;
						
			//lets make some general information						
			$userdata=array(
				"username"=>"fb_".$user_info['username'],
				"password"=>$fakepassword,
				"email"=>$user_info['email'],	//we ask for this permission on the loginurl creater
			);
								
			$model->attributes=$userdata;				
			
			$model->password=UserModule::encrypting($fakepassword);	//set the password				
			$model->lastvisit=$model->createtime=time();			//set create andvisit time
			$model->superuser = 0;									//probably not
			$model->status = 1;										//active
			$model->regmethod = "facebook";							//stats for me
			$model->fbid = $user_info['id'];						//set the fbid
			
			
			//these are profile feilds (you can probably omit)
			$profile->firstname = $user_info['first_name'];
			$profile->lastname = $user_info['last_name'];
			$profile->birthday = "2000-01-01"; 
			
			//try and save the new user
			if ($model->save()) {
				//word it saved 
				//again since i have a profile for each user aswell
				$profile->user_id=$model->id;
				$profile->save();
				
				//now lets log them in					
				$identity=new UserIdentity($userdata['username'],$fakepassword);
				$identity->authenticate();
				$duration=3600*24*30;//for a month
				Yii::app()->user->login($identity,$duration);
				
				//and were gone
				$this->redirect(Yii::app()->homeUrl);
			}else{
				echo "FAIL<br><pre>";
				print_r($model->getErrors());						
				die();
			}
		}else{
			//so this user exists
			//lets log tim in
			$identity=new UserIdentity("fb_".$user_info['username'],$fakepassword);
			$identity->authenticate();
			$duration=3600*24*30;
			Yii::app()->user->login($identity,$duration);
			//away they go.
			$this->redirect(Yii::app()->homeUrl);
		}

	}	
	
}












