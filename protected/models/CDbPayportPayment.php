<?php

/**
 * This is the model class for table "paypal_payment".
 *
 * The followings are the available columns in table 'paypal_payment':
 * @property integer $id
 * @property string $get_json
 * @property string $post_json
 * @property integer $status
 * @property integer $type
 * @property string $creationdate
 * @property string $modificationdate
 * @property string $ipaddress
 * @property string $payment_json
 */
class CDbPayportPayment extends CActiveRecord
{   
        const CONST_FIELD_STATUS_IS_VALID=1;
        const CONST_FIELD_STATUS_IS_INVALID=2;
        
        const CONST_FIELD_TYPE_IS_PAYPAL=1;
        
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CDbPayportPayment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
        
        /**
         * 创建一个临时的付款信息
         * @return CDbPayportPayment
         */
        public static function Creation(){
            $oper=new CDbPayportPayment();
            $oper->ipaddress=$_SERVER['REMOTE_ADDR'];
            $oper->creationdate=$oper->modificationdate=date('Y-m-d H:i:s');
//            $oper->status=
        }

        /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'payport_payment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('get_json, post_json, status, type, creationdate, modificationdate, ipaddress, payment_json', 'required'),
			array('status, type', 'numerical', 'integerOnly'=>true),
			array('ipaddress', 'length', 'max'=>16),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, get_json, post_json, status, type, creationdate, modificationdate, ipaddress, payment_json', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'get_json' => 'Get Json',
			'post_json' => 'Post Json',
			'status' => 'Status',
			'type' => 'Type',
			'creationdate' => 'Creationdate',
			'modificationdate' => 'Modificationdate',
			'ipaddress' => 'Ipaddress',
			'payment_json' => 'Payment Json',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('get_json',$this->get_json,true);
		$criteria->compare('post_json',$this->post_json,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('type',$this->type);
		$criteria->compare('creationdate',$this->creationdate,true);
		$criteria->compare('modificationdate',$this->modificationdate,true);
		$criteria->compare('ipaddress',$this->ipaddress,true);
		$criteria->compare('payment_json',$this->payment_json,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}