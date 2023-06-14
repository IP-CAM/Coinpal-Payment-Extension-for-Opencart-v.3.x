# OpenCart Coinpal Checkout Installation

## Step 1: Log in to the Coinpal Admin Dashboard to get the Merchant Number and Secret Key.
1. [Register](https://portal.coinpal.io/#/admin/register)/[login](https://portal.coinpal.io/#/admin/login) and go to Coinpal's Admin Dashboard 
![](./img/register.png)

2. Follow the Dashboard guidelines to fill in the relevant information
![](./img/kyb.png)
3. Click the 'Integration' button in the lower left corner to get the corresponding Merchant Id and Secret Key
![](./img/api-key.png)

## Step 2: Installing the Coinpal Plugin on your OpenCart Site.
1. Click on the [Coinpal plugin](https://github.com/coinpal-io/plug_opencart/blob/master/coinpal.ocmod.zip) to download the Coinpal OpenCart Payment Plug.

2. Navigate to your OpenCart admin area and follow this path: plugins management -> plugins installation -> upload.

![](./img/upload.png)

3. Activate the Coinpal OpenCart Gateway

    Go to your OpenCart admin area and click on plugins management -> plugins management -> payment module

    Find the payment method named 'Coinpal' and click 'install'.
    
![](./img/install.png)

Copy and Paste all of the Settings you generated in your Coinpal Dashboard on Step #1.

Click Save Changes.

![](./img/edit.png)


## Step 3: Testing your Coinpal OpenCart Integration.

To confirm your Integration is properly working create a test order:

Add a test item to your shopping cart and view the cart.

Proceed to Checkout

Select 'Pay Crypto with Coinpal' as the payment method.

Click Confirm Order

![](./img/checkout.png)

Click the “Confirm Order” button.

Verify all of the Wallet Addresses and Order info, and make sure the Validation Tests all have a Green Check Mark.

If you like you can now proceed to making a test payment.

![](./img/checkout2.png)





## Step 4: Marking a Payment as Received on OpenCart.

Login to your OpenCart Admin Dashboard.

Go to the OpenCart Section and Click Orders.

You will see the test orders marked as “Paid”.

Check whether coins are settled to the CoinPal wallet.

You may also use a Block Explorer to verify if the transaction was processed.

After the verification of the above steps is completed, it means that the connection with Coinpal is successful.





