Todo

## ContactUsGraphQL

For contacting GraphQL to an existing module, we first have to create schema.graphqls which defines the GraphQL types which we want to create.

```
@doc(description: "Contact us mail")  
type Mutation {  
    contactUs (  
        name: String! @doc(description: "Name of the customer")  
        email: String! @docenter link description here(description: "Mail of the customer")  
        phone: String! @doc(description: "Phone number of the customer")  
        message: String! @doc(description: "Message to send")  
    ): ContactUsResponse  
    @resolver(class: "Codilar\\ContactUsGraphQL\\Model\\Resolver\\SendMail")  
}

```

Here, we have used Mutation type named contactUs to send the inputs (name, email, phone, message) to the resolver class given:

```
@resolver(class: "Codilar\\ContactUsGraphQL\\Model\\Resolver\\SendMail")  

```

A mutation has to have both an input and output, input takes the required fields and output defines the return values to be given. Here, the output is defined as another type which we are naming it as contactUsResponse.

The definition of ContactUsResponse given as:

```
@doc(description: "Response message")  
type ContactUsResponse {  
    successMessage: String  
}

```

Here, we are returning a String datatype named as “successMessage”.

Now create the resolver class which we have mentioned in `Model/Resolver` directory with the name: `SendEmail.php`

The class have to implement `ResolverInterface` defined in `Magento\Framework\GraphQl\Query\ResolverInterface` .

```
class SendMail implements ResolverInterface
{
}

```

We are using a helper class named `Helper` to perform the operations of the resolver class.

Create a class Helper in the same namespace as that of resolver: `Codilar\ContactUsGraphQL\Model\Resolver`

```
class Helper
{
}

```

Initialize the instance of Helper class in constructor of `SendMail`

```
public function __construct(  
 Helper $dataHelper  
) {  
  $this->dataHelper = $dataHelper;  
}

```

Define the `resolve()` function declared in the `ResolverInterface` to get the inputs from GraphQL and define the necessary codes for our operation.

```
public function resolve(  
 Field $field,  
  $context,  
  ResolveInfo $info,  
 array $value = null,  
 array $args = null  
) {  
  if (empty($args['name']) || empty($args['email']) || empty($args['phone']) || empty($args['message'])) {  
  throw new GraphQlInputException(__('All fields must be specified'));  
  }  
  $name = $args['name'];  
  $email = $args['email'];  
  $phone = $args['phone'];  
  $message = $args['message'];  
  $successMessage = $this->dataHelper->contactUs(  
  $name,  
  $email,  
  $phone,  
  $message  
  );  
 return $successMessage;  
}

```

We are taking the inputs and passing it to the function `contactUs()` declared in our `Helper` class.

```
public function contactUs($name, $email, $phone, $message)  
{  
  $thanks_message = [];  
 try {  
  $this->sendEmail($name, $email, $phone, $message);  
  $thanks_message['successMessage'] = 'Thanks for contacting us. We will get back to you soon';  
  } catch (Exception $e) {  
  $thanks_message['successMessage'] = 'Error sending the mail. Please try again.';  
  $this->logger->addWarning($e->getMessage());  
  }  
  return $thanks_message;  
}

```

The `return $thanks_message;` provides the response to the GraphQL mutation.

The `sendEmail()` function defines the code to send the mail using `send()` method declared in `MailInterface` present in `Magento\Contact\Model\`.

```
public function sendEmail($name, $email, $phone, $message)  
{  
  $form_data = [];  
  $form_data['name'] = $name;  
  $form_data['email'] = $email;  
  $form_data['telephone'] = $phone;  
  $form_data['comment'] = $message;  
  $form_data['hideit'] = "";  
  $form_data['form_key'] = $this->formKey->getFormKey();  
  $this->mail->send($email, ['data' => new DataObject($form_data)]);  
}

```

The constructor of the `Helper` should also initialize `DataPersistorInterface` from `Magento\Framework\App\Request\` in order to keep session and `FormKey` in `Magento\Framework\Data\Form\` to pass the form template.

```
public function __construct(
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        Logger $logger,
        FormKey $formKey
    ) {
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->formKey = $formKey;
        $this->logger = $logger;
    }

```
To perform the GraphQL, we need to visit a GraphQL Playground and set the endpoint as `<YOUR_MAGENTO_LOCATION_PATH/graphql` , which in my case is, `http://127.0.0.1/magento24/graphql`.

Since we defined the type as mutation, in the code tab, we need to type the keyword `mutation` first and then open the braces, `mutation {
}` . Inside the mutation, we need to give the name of the mutation which we defined and give the input parameters inside paranthesis, and the output parameters inside curly braces.

In our case, we defined `contactUs` method which takes input of `name, email, phone, message` and outputs `successMessage`. So it can be given as:

    mutation {
      contactUs(name: "Demo" email: "demo@mail.com"
        phone:"1234567890" message:"Demo"){
	        successMessage
      }
    }
We get the output as this:

    {
      "data": {
        "contactUs": {
          "successMessage": "Thanks for contacting us. We will get back to you soon"
	        }
	     }
     }
   
When we trigger this GraphQL mutation, a mail with the given inputs will be sent to the mail which is given in the Magento Backend.
 
## Logger

In order to store the logs of the module in a separate file, first we have to add the following types to `di.xml` .

```
<type name="Codilar\ContactUsGraphQL\Logger\Handler">  
     <arguments> 
	     <argument name="filesystem" xsi:type="object">
		     Magento\Framework\Filesystem\Driver\File
	     </argument>  
     </arguments>
 </type>

```

The `type name=""` is the class which we use to log the data.

Now define another `type` which is used to redirect the class to another class whenever it is called.

```
<type name="Codilar\ContactUsGraphQL\Logger\Logger">  
     <arguments> 
	     <argument name="name" xsi:type="string">
		     ContactUsGraphQlLogger
	     </argument>  
	     <argument name="handlers" xsi:type="array">  
		     <item name="system" xsi:type="object">
			     Codilar\ContactUsGraphQL\Logger\Handler
		     </item>  
	     </argument> 
     </arguments>
 </type>

```

Create the class `Logger` in Logger directory which extends the `Logger` class in `Monolog\Logger` .

```
 <?php  
  
namespace Codilar\ContactUsGraphQL\Logger;  
  
use Monolog\Logger as ExtendLogger;  
  
class Logger extends ExtendLogger  
{  
}

```

Create `Handler` class which extends the `Base` class in `Magento\Framework\Logger\Handler\` and declare tye type of logs and the custom file to store the logs.

```
namespace Codilar\ContactUsGraphQL\Logger;  
  
use Magento\Framework\Logger\Handler\Base as BaseHandler;  
use Monolog\Logger;  
  
class Handler extends BaseHandler  
{  
  /**  
   * Logging level * * @var int  
   */
   protected $loggerType = Logger::INFO;  
  
  /**  
   * File name * * @var string  
   */
   protected $fileName = '/var/log/contact_us_graphql_logger.log';  
}

```

The log will be stored in Magento folder inside `/var/log/` in `contact_us_graphql_logger.log` file.

Now to log the errors, import the `Logger` class inside the class we want and initialize it’s object in the constructor.

```
public function __construct(Logger $logger)
 {
     $this->logger = $logger;
 }

```

We can catch the exception and write it to the logs like this:

```
try {
} catch (\Exception $exception) {
	$this->logger->addWarning($e->getMessage());
}

```
