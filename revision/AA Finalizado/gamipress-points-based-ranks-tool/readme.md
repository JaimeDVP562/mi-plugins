# GamiPress Points-Based Ranks Tool

## Description

This plugin provides a tool to automatically generate multiple ranks with point‑based requirements. It allows site administrators to quickly create rank systems where users unlock new ranks as they accumulate points.

## Requirements

- GamiPress plugin (active)

## Installation

1. Add the `gamipress-points-based-ranks-tool` folder to `/wp-content/plugins/`
2. Activate the plugin from the “Plugins” menu in WordPress (Make sure GamiPress is installed and active, otherwise this will not work).

## Usage

### Accessing the Tool

1. Go to **GamiPress → Tools** in the WordPress admin
2. You will see a new section called **Points-Based Ranks Builder**.

### Creating Ranks

1. **Points Type**: Select the points type.
2. **Rank Type**: Select the rank type.
3. **Points Step**: Enter how many points the user will need to reach the next rank.  
   For example, if you enter 100, the user will need:  
   0 points for rank 1, 100 points for rank 2, 200 for rank 3, etc.
4. **Number of Ranks**: Enter how many ranks you want to create.
5. **Name Pattern**: Enter the naming pattern using placeholders:
   - `{number}`: replaced with sequential numbers (1, 2, 3…)
   - `{letter}`: replaced with letters (A, B, C… Z, AA, AB…)
6. **(Optional) Generate Rank Images**: Check this to automatically generate badge images.
7. **(Optional) Colors**: Configure the primary, secondary, and text colors for the badges.
8. Click **Execute** to create the ranks (in batches of 20).
9. After creating the ranks, a button called **Award Existing Users** will appear.  
   When clicked:

    1. It checks the point balances of all existing users.
    2. It awards ranks to users who meet the point requirements.

## How to Test

1. **Prerequisites**:
   - Install and activate GamiPress.
   - Ensure at least one points type exists.
   - Ensure at least one rank type exists.

2. **Steps**:
   - Go to **GamiPress → Tools**.
   - Go to the new section called "Points-Based Ranks Builder".
   - Select a **Points Type**.
   - Select a **Rank Type**.
   - Set **Points Step**.
   - Set **Number of Ranks**.
   - Set **Name Pattern**.
   - Optionally enable the checkbox to generate a rank image, selecting the primary, secondary, and text colors you want.
   - Click **Execute**.
   - Optionally, if you already have users with points, click **Award Existing Users** so that users who already have enough points receive the newly created ranks.

3. **Expected Results**:
   - X new ranks are created (depending on what you set in **Number of Ranks**).
   - Each rank (except the first) should have a requirement indicating that to obtain it, the user must match or exceed the points set in **Points Step**.

4. **Verification**:
   - Go to **Ranks** and select the Rank type chosen earlier.
   - Confirm that the new ranks appear with the correct names and order.
   - If you generated a rank image, check that it displays correctly.
   - Click each rank to verify that the point requirement is correctly configured.
   - Then go to **Users**, click on a user who has no points, and assign enough points to meet the rank requirement. You will see that the user receives the ranks.
   - If you previously clicked the **Award Existing Users** button, users who have points and meet the rank requirement should have received the ranks.


## Note
   - The **Number of Ranks** field is not requested in the original specification, but we believe it is necessary since the required number of ranks to create is not indicated anywhere. With this field, the user can decide the limit they need.