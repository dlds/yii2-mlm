Multi level marketing rewrds module
===
Module handles rewards generating from appropriate subjects.

Installation
---

`composer require dlds/yii2-mlm`

What is MLM?
---
MLM is referal marketing system where user get reward for succesfull produc sharing/solding. It is similar to Affiliate marketing but has more then one rewards level.
In MLM you build your own structure (own team) of users.

Exmaple:

| –– YOU  
| –––– BOB *(1st level rewards)*     
| –––––––– ROBERT *(2st level rewards)*     
| –––– ALICE *(1st level rewards)*  
| –––––––– JIMMY *(2st level rewards)*    
| –––––––– GEORGE *(2st level rewards)*  
| ––––––––––––– MARK *(3st level rewards)*  

In this scenario you will get some reward when any of 6 users in your structure buy a product.

Entities in Module
---
Folowing naming and entities are used in MLM module.

`Subject`

Class which is used as source for reward generation. It is usually some sort of **ProductOrder** or other entity which works with user payments.

`Participant`

Class which represents user identity among MLM structure.

`Reward`

Class which represents single rewards entry.

Implementation of module
---

First of you have to prepare your database. There is MySQL Workbench schema / SQL import script showing simple example of DB structure required for implementation in [app/data](./app/data/schema.sql) folder. 

There are following tables:

1. subject
2. participant
3. rwd_basic
4. rwd_extra
5. rwd_custom

> As you can see there are 3 types of rewards. There is no need to use all 3 types. Standart rewards are held in table rwd_basic. More about rewards types in secrion Reward Types.

Then you have to implement following interfaces into your application.

`MlmSubjectInterface`

This interfaces is usually implemented by something like **ProductOrder.php**

`MlmParticipantInterface`

This interfaces is usually implemented by something like **UsrIdentityModel.php**

`MlmRewardInterface`

This interfaces is usually implemented by something like **OrderReward.php**


How module works?
---
After required implementation the rewards generation and verification is processed automatically by cron and console commands.

When `Subject` is ready to generate rewards module will automatically generate appropiate amount of rewards based on module setup.

During creation each participant is verified if is eligible to take rewards.

After then each of this rewards is verified when reach the end of protection period of time (e.g. 14 days in which the subject order can be cancelled by user)

After that reward is approved and is ready to pay off. Paying off handling is not part of this module yet.

Module Setup
---
Module provides bunch of setup options which can change the rewards generation.

`rules` array

Defines procentual amount of subject price will be used for each level of reward.

Example showing 40% of subject price is pushed into rewards and spread among 5 levels: 
``` php 
[
    1 => 20,
    2 => 10,
    3 => 5,
    4 => 3,
    5 => 2
]
```

`limitRules` int

Restriction for rules configuration. Defines maximal procentual sum of all rules. In example above 40 will be suitable.

`delayPending` int (unix format)

Indicates how long will every single reward be in protection state. During this time the reward will have PENDING state. After this delay the APPROVED/DENY state will be used.

For 1 day the value 86400 must be used. It is unix value for one day: 14 * 24 * 60 * 60 = 86400 

`roundPrecision` int

Number of decimal places for reward value.

`roundMode` enum (Mlm::MLM_ROUND_UP | Mlm::MLM_ROUND_DOWM) 

Indicates if reward value will be rounded up or down.

`skipWorthlessRewards` boolean

When true the zero reward or rewards which value is 0.0000... after rounding will be ignored.

`clsParticipant` string

Participant model class name in your application.

`clsRewardBasic` string

Basic Rewards model class name in your application.

`clsSubjects` array

Class names of all subjects in your application. You can have 1 to N subjects defined in this array and all will get rewards.

`isCreatingActive` boolean

Enables / disables reward creation.

`isVerifyingActive` boolean

Enables / disables reward verification. Verification is processed after protection period.

`isLevelRestrictionAllowed`

When true the rewards will be created with special verification on participant. As default the participant is verified only if is eligible to take rewards. But when Level Restriction is Allowed the participant will be verified if is eligible to take reward of specific level in structure.

This means that you can restrict some participant to be able to take only 1st and 2nd level of rewards and another participant to be able to take all levels of rewards.  
  
 


