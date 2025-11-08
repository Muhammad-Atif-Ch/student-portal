const jwt  = require('jsonwebtoken');
const axios = require( 'axios');
const  fs = require( 'fs');
const path = require ('path');
 
const APPLE_ISSUER_ID = '59657095-081e-43e1-a6df-25491de40042';
const APPLE_KEY_ID = 'KYZT3B6GHH';
const APPLE_BUNDLE_ID = 'com.dtt-car-bike-ireland';
 
async function getHello() {
    const privateKey = fs.readFileSync(path.join(process.cwd(), 'SubscriptionKey_KYZT3B6GHH.p8'), "utf8");
// console.log('privateKey',privateKey)
    function generateAppleToken() {
      //@ts-ignore
      return jwt.sign(
        {
          iss: APPLE_ISSUER_ID,
          iat: Math.floor(Date.now() / 1000),
          exp: Math.floor(Date.now() / 1000) + 300,
          aud: 'appstoreconnect-v1',
          bid: APPLE_BUNDLE_ID,
        },
        privateKey,
        {
          algorithm: 'ES256',
          header: { alg: 'ES256', kid: APPLE_KEY_ID, typ: 'JWT' },
        },
      );
    }
 
    try {
      const token = generateAppleToken();
      console.log('token', token);
      const originalTransactionId = '2000001050029563';
 
      const res = await axios.get(
        `https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/${originalTransactionId}`,
        { headers: { Authorization: `Bearer ${token}` } },
      );
      console.log('res', res?.data);
      if(res.status == 200){
      const sub = res.data.data?.[0].lastTransactions?.[0];
 
  const transaction = JSON.parse(
    Buffer.from(sub.signedTransactionInfo.split('.')[1], 'base64').toString()
  );
  const renewal = JSON.parse(
    Buffer.from(sub.signedRenewalInfo.split('.')[1], 'base64').toString()
  );
  console.log('transaction',transaction)
  console.log('renewal',renewal)
      return { res:res?.data };
}
    } catch (error) {
      console.log({error})
      return { error };
    }
  }

  getHello()

 