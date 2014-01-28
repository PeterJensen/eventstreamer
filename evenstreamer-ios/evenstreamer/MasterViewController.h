//
//  MasterViewController.h
//  evenstreamer
//
//  Created by Stephan Lee on 1/23/14.
//  Copyright (c) 2014 Stephan Lee. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "LeftPanelViewController.h"

@protocol CenterViewControllerDelegate <NSObject>

@optional
- (void)movePanelLeft;
- (void)movePanelRight:(BOOL)fullscreen;

@required
- (void)movePanelToOriginalPosition;
- (void)togglePanelRight;
@end


@interface MasterViewController : UITableViewController <LeftPanelViewControllerDelegate>

@property (nonatomic, assign) id<CenterViewControllerDelegate> delegate;

@property (nonatomic, strong) NSString *event;

@property (nonatomic, strong) NSMutableDictionary *cachedImages;

@property (nonatomic, weak) IBOutlet UIBarButtonItem *leftButton;

@end
